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
                var form = $("#signUpForm");

                var emailRegExp = new RegExp("^[a-zA-Z0-9\-\.]+@[a-zA-Z0-9\.\-]+$", "ug");
                var passRegExp = new RegExp("^[a-zA-Z0-9\-\.]+$", "ug");
                var onlyLettersRegExp = new RegExp("^[a-zA-Z]+$", "ug");

                var smthEmpty = false;

                var msg, msgColor = "red";

                var isValid = false;

                for (const elem of form.serializeArray())
                    if (elem.value == "")
                    {
                        msg = "Something's empty";
                        smthEmpty = true;
                        break;
                    }
                
                if (!smthEmpty)
                {
                    if (!emailRegExp.test(form[0].email.value))
                        msg = "Not an email.";
                    else if(form[0].nickname.value.length > 15)
                        msg = "Too long nickname";
                    else if (!passRegExp.test(form[0].password.value))
                        msg = "Invalid password.";
                    else if (form[0].password.value.length < 4)
                        msg = "Too short password.";
                    else if (form[0].password.value.length > 15)
                        msg = "Too long password.";
                    else if (!onlyLettersRegExp.test(form[0].username.value))
                        msg = "Invalid user name";
                    else if (form[0].username.value.length > 15)
                        msg = "Too long user name";
                    else if (isNaN(form[0].age.value))
                        msg = "Invalid age";
                    else if (!isNaN(form[0].age.value) && (form[0].age.value < 5 || form[0].age.value > 100))
                        msg = "Invalid age";
                    /*else if (!onlyLettersRegExp.test(form[0].country.value))
                        console.log("Invalid country");
                    else if (onlyLettersRegExp.test(form[0].country.value) && (form[0].country.value.length < 3 || form[0].country.value.length > 100))
                        console.log("Invalid country");*/
                    else
                    {
                        var data = $(this).serialize();

                        var ajax = 
                        $.ajax(
                            {
                                cache: false,
                                method: "post",
                                url: "engine/actions/auth/signup.php",
                                data: data,
                                success: 
                                    function (result)
                                    {
                                        var arr = JSON.parse(result);
                                        return arr;
                                    },
                                complete: 
                                    function (r) 
                                    {
                                        alert(r.responseText);
                                    }
                            }
                        );
                        //console.log(ajax.responseText);
                    }
                }

                $("#signUpForm .msg").html(msg);
                $("#signUpForm .msg").css("color", msgColor);

                return false;
            }
        );
    }
);