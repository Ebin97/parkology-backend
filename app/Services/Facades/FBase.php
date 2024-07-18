<?php


namespace App\Services\Facades;

use App\Helper\_EmailHelper;
use App\Helper\_MediaHelper;
use App\Services\Interfaces\IBase;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;

class FBase implements IBase
{
    protected $model,
        $table,
        $translatable = false,
        $translatableColumn = [],
        $columns, $allColumns,
        $rules,
        $where = [],
        $hashing = false,
        $hashingColumn = "password",
        $search,//search columns
        $slug, //to compare column if it`s already exist or not
        $slugging,//this is the column will use it to extract the slug
        $private,//Use it to check the permission, as we will use it to check whether the user has permission to access this records or not
        $privateInstance,//the table will use it to check the permission
        $privateColumn,//the column will use it to check the permission
        $selectedColumn,//the column will get it after check the permission
        $privateId,//the id for the users
        $trackExist,//check if auth id will store it in table
        $trackId,//the auth id
        $trackColumn,//the auth column in track table
        $encrypt = false,// to check if this table has encrypt column
        $encryptColumn,//name of the encrypt column
        $unique,
        $hasUnique,
        $orderBy = "created_at",
        $verificationEmail,//to check if this model can send verification email or not
        $dateColumns;

    public function __instance()
    {
        return new $this->model;
    }

    public function _instancePrivate()
    {
        return new $this->privateInstance;
    }

    public function validation(Request $request)
    {
        try {
            $request->validate($this->rules);
            return true;
        } catch (ValidationException $exception) {
            return $exception;
        }
    }

    public function getColumn(Request $request)
    {
        $columns = [];
        if ($this->slug) {

            if (in_array($this->slugging, $this->translatableColumn)) {
                $value = [
                    'en' => Str::slug($request->input($this->slugging . '.en')),
                    'ar' => Str::slug($request->input($this->slugging . '.ar')),
                ];
                $slug = $value;
            } else {
                $slug = Str::slug($request->input($this->slugging));
            }
            $columns = [$this->unique => $slug];
        }
        $all = $request->all();
        foreach ($all as $key => $item) {
            if (($k = array_search($key, $this->columns)) !== false) {
                if (in_array($key, $this->translatableColumn)) {
                    $value = [
                        'en' => $item['en'],
                        'ar' => $item['ar'],
                    ];
                    $columns = array_merge($columns, [$key => $value]);
                } else {
                    $columns = array_merge($columns, [$key => $item]);
                }
            }
        }
        if ($this->encrypt) {
            $columns[$this->encryptColumn] = Hash::make($columns[$this->encryptColumn]);
        }
//        if ($this->hashing) {
//            $columns[$this->hashingColumn] = Hash::make($columns[$this->unique]);
//        }
        if ($this->trackExist) {
            $columns[$this->trackColumn] = $this->trackId;
        }
        return $columns;
    }

    public function getAllColumn(Request $request)
    {
        $columns = [];
        if ($this->slug) {

            if (in_array($this->slugging, $this->translatableColumn)) {
                $value = [
                    'en' => Str::slug($request->input($this->slugging . '.en')),
                    'ar' => Str::slug($request->input($this->slugging . '.ar')),
                ];
                $slug = $value;
            } else {
                $slug = Str::slug($request->input($this->slugging));
            }
            $columns = [$this->unique => $slug];
        }
        $all = $request->all();
        foreach ($all as $key => $item) {
            if (($k = array_search($key, $this->allColumns)) !== false) {
                if (in_array($key, $this->translatableColumn)) {
                    $value = [
                        'en' => $item['en'],
                        'ar' => $item['ar'],
                    ];
                    $columns = array_merge($columns, [$key => $value]);
                } else {
                    $columns = array_merge($columns, [$key => $item]);
                }
            }
        }
        if ($this->encrypt) {
            if (isset($columns[$this->encryptColumn])) {
                $columns[$this->encryptColumn] = Hash::make($columns[$this->encryptColumn]);
            }
        }
        if ($this->hashing) {
            if (isset($columns[$this->unique])) {
                $columns[$this->hashingColumn] = Hash::make($columns[$this->unique]);
            }
        }
        if ($this->trackExist) {
            if (isset($columns[$this->trackColumn])) {
                $columns[$this->trackColumn] = $this->trackId;
            }
        }
        return $columns;
    }


    public function index(Request $request)
    {
        $temp = $this->__instance()->query();
        if ($request->has('q')) {
            $queryList = explode(' ', $request->input('q'));
            foreach ($queryList as $queryItem) {
                foreach ($this->search as $item) {
                    $temp = $temp->where($item, 'like', '%' . $queryItem . '%');
                }
            }
        }
        if (count($this->where) > 0) {
            $temp = $temp->where($this->where);
        }
        if ($this->private) {
            $temp = $temp->whereIn('id', $this->_instancePrivate()->query()->where($this->privateColumn, $this->privateId)->select($this->selectedColumn)->pluck('event_id')->toArray());
        }

        return $temp->orderBy($this->orderBy, 'desc')->get();
    }

    public function getByColumns($columns)
    {
        return $this->__instance()->query()->where($columns);
    }

    public function getByDate(Request $request)
    {
        $temp = $this->__instance()->query();
        if ($request->has('q')) {
            $queryList = explode(' ', $request->input('q'));
            foreach ($queryList as $queryItem) {
                foreach ($this->search as $item) {
                    $temp = $temp->where($item, 'like', '%' . $queryItem . '%');
                }
            }
        }
        if ($request->has('queryDate')) {
            $queryDate = $request->input('queryDate');
            $start_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "00:00:00");
            $end_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "23:59:59");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '>=', $start_date)->whereDate($dateColumn, '<=', $end_date);
                }
            }
        }
        if ($request->has('startDate')) {
            $queryDate = $request->input('startDate');
            $start_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "00:00:00");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '>=', $start_date);
                }
            }
        }
        if ($request->has('endDate')) {
            $queryDate = $request->input('endDate');
            $end_date = Carbon::createFromFormat('Y/m/d H:i:s', $queryDate . "23:59:59");
            if ($this->dateColumns) {
                foreach ($this->dateColumns as $dateColumn) {
                    $temp = $temp->whereDate($dateColumn, '<=', $end_date);
                }
            }
        }
        if ($this->private) {
            $temp = $temp->whereIn('id', $this->_instancePrivate()->query()->where($this->privateColumn, $this->privateId)->select($this->selectedColumn)->pluck('event_id')->toArray());
        }
        return $temp->orderBy($this->orderBy, 'desc')->get();
    }

    public function store(Request $request)
    {
        $ex = $this->validation($request);
        if (($ex instanceof ValidationException)) {
            throw new ValidationException($ex->validator, $ex->getMessage());
        }
        if (!$this->checkDuplicate($request)) {
            return null;
        }
        return $this->__instance()->create($this->getColumn($request));
//        if ($this->verificationEmail) {
//            event(new Registered($model));
////            $email = new _EmailHelper();
////            $email->sendVerification($model);
//        }
        // return $model;
    }

    public function update(Request $request, $id)
    {
        $ex = $this->validation($request);
        if (($ex instanceof ValidationException)) {
            throw new ValidationException($ex->getMessage(), $ex->getMessages());
        }
        $item = $this->getById($id);
        if ($item) {
            if (!$this->checkDuplicate($request, $id)) {
                return null;
            }
            $item->update($this->getColumn($request));
        }
        return $item;
    }

    public function delete($id)
    {
        return $this->__instance()->query()->where(['id' => $id])->delete();
    }

    public function getById($id)
    {
        return $this->__instance()->query()->where(['id' => $id])->first();
    }

    public function getBySlug($slug)
    {
        return $this->__instance()->query()->where(['slug' => $slug])->first();
    }

    public function checkUnique($value, $key, $id = null)
    {

        if ($id) {
            return $this->__instance()->query()->where([$key => $value])->where('id', '!=', $id)->first();
        }
        return $this->__instance()->query()->where([$key => $value])->first();
    }

    public function checkDuplicate(Request $request, $id = null)
    {
        if ($this->hasUnique) {
            $value = "slug";
            switch ($this->unique) {
                case "slug":
                    if (in_array($this->slugging, $this->translatableColumn)) {
                        $value = [
                            'en' => Str::slug($request->input($this->slugging . '.en')),
                            'ar' => Str::slug($request->input($this->slugging . '.ar')),
                        ];
                    } else {
                        $value = Str::slug($request->input($this->slugging));
                    }
                    break;
                case "phone":
                case "email":
                    $value = $request->input($this->unique);
                    break;
                default:
                    break;
            }
            if ($res = $this->checkUnique($value, $this->unique, $id)) {
                throw new Exception('Account is already exist');
            }
        }
        return true;
    }

    public function uploadFile($obj, $destinationPath, $image, $filename)
    {
        $imgFile = Image::make($image->getRealPath());
        $imgFile->resize(600, 300, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $filename);
        return $obj->images()->create([
            'url' => $filename,
            'thumb_url' => $filename,
            'mime_type' => 'image',
            'type' => 'image',
        ]);
    }

}
