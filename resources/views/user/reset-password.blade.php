<?php
?>
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Parkology</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
            integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
            integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <style>
        .pass_show {
            position: relative
        }

        .pass_show .ptxt {

            position: absolute;

            top: 50%;

            right: 10px;

            z-index: 1;

            color: #f36c01;

            margin-top: -10px;

            cursor: pointer;

            transition: .3s ease all;

        }

        .pass_show .ptxt:hover {
            color: #333333;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-4 offset-4 mt-5">
            <form action="{{route('update.password',['token'=>$token])}}" method="post">
                @csrf
                <div class="form-group">
                    <label>New Password</label>
                    <div class="form-group pass_show">
                        <input type="password" value="" class="form-control" name="password"
                               placeholder="New Password"/>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="form-group pass_show">
                        <input type="password" value="" class="form-control" name="password_confirmation"
                               placeholder="Confirm Password"/>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">Update</button>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        $('.pass_show').append('<span class="ptxt">Show</span>');
    });


    $(document).on('click', '.pass_show .ptxt', function () {

        $(this).text($(this).text() == "Show" ? "Hide" : "Show");

        $(this).prev().attr('type', function (index, attr) {
            return attr == 'password' ? 'text' : 'password';
        });

    });
</script>
</body>
</html>


