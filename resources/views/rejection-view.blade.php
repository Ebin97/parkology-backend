<?php
?>

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My HTML5 Page</title>
    <!-- Bootstrap CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css"
        rel="stylesheet"
    />
    <!-- Select2 CSS -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css"
        rel="stylesheet"
    />
    <style>
        body {
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f7f7f7;
        }

        .container-md {
            min-height: 70vh;
            width: 100%;
            max-width: 1000px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        header {
            text-align: center;
        }

        main {
            margin-top: 20px;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
        }

        .flex {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container-md bg-white h-100">
    <header>
        <div class="alignment" align="center" style="line-height: 10px">
            <div style="max-width: 240px">
                <img
                    src="https://d15k2d11r6t6rl.cloudfront.net/pub/bfra/8adpurz1/5ew/npn/2gi/logo.png"
                    class="img-fluid"
                    alt="Logo"
                />
            </div>
        </div>
    </header>

    <main>
        <h2 class="text-center mb-4">Receipt Information</h2>
        @if($sale)
            <div class="flex mb-5">
                <table class="table table-hover mb-5">

                    <tbody>
                    <tr>
                        <th scope="row">Ref of Receipt</th>
                        <td>{{$sale['receipt_number']}}</td>
                    </tr>
                    <tr>
                        <th scope="row">User Name</th>
                        <td>{{$sale['name']}}</td>
                    </tr>
                    <tr>
                        <th scope="row">Date of Receipt</th>
                        <td>{{$sale['receipt_date']}}</td>
                    </tr>
                    </tbody>

                </table>
                <div class="alignment" align="center" style="line-height: 10px">
                    <div style="max-width: 400px">
                        <img
                            src="{{$sale['media']}}"
                            class="img-fluid"
                            alt="Receipt"
                        />
                    </div>
                </div>

            </div>

            <table class="table table-hover">
                <thead>
                <tr>
                    <th scope="col">Product</th>
                    <th scope="col">Packs</th>
                </tr>
                </thead>
                <tbody>
                @foreach($sale['items'] as $item)
                    <tr>
                        <th scope="row">{{$item['product']}}</th>
                        <td>{{$item['packs']}}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>

        @endif
        <h4 class="text-center mb-2 mt-5">
            Please select the reason(s) for rejecting the receipt:
        </h4>
        <form method="post" action="{{route('rejectPost',['id'=>$id])}}">
            @csrf
            <div class="mb-3">
                <select
                    id="reason"
                    name="reasons[]"
                    class="form-select "
                    multiple="multiple"
                >
                    <option value="">Reason for rejection</option>
                    @foreach($reasons as $item)
                        <option value="{{$item->id}}">{{$item->title}}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3 text-center">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </main>
</div>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $("#reason").select2({
            placeholder: "Select a reason for rejection",
            width: "100%",
            allowClear: true,
            tags: true,
            tokenSeparators: [",", " "],
            dropdownParent: $("#reason").parent(),
        });
    });
</script>
</body>
</html>


