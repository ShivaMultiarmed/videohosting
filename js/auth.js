"use strict";

$(document).ready(
    function ()
    {
        $("#loginForm").submit(
            function ()
            {
                $(".msg").css("color", "red");

                var data = $(this).serialize();
                if ($("#loginForm input[name='nickname']").val() == "" || $("#loginForm input[name='password']").val() == '') 
                {
                    $(".msg").html("Something's empty.");
                }
                else if ($("#loginForm input[name='nickname']").val().length > 127 || $("#loginForm input[name='password']").val().length > 127)
                {
                    $(".msg").html("Too long nickname or password.");
                }
                else
                    $.ajax(
                        {
                            cache: false,
                            method: "post",
                            url: "engine/actions/auth/login.php",
                            data: data,
                            success: 
                                function (response)
                                {
                                    var arr = JSON.parse(response);
                                    $(".msg").html(arr["msg"]);
                                    if (arr["isErr"])
                                        $(".msg").css("color", "green");
                                }
                        }
                    );
                return false;
            }
        );
        $("#signUpForm").submit(
            function ()
            {
                var data = $(this).serialize();

                $.ajax(
                    {
                        cache: false,
                        method: "post",
                        url: "engine/actions/auth/signup.php",
                        data: data,
                        success: 
                            function (result)
                            {
                                console.log(result);
                            }
                    }
                );

                return false;
            }
        );
    }
);