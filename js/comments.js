"use sctrict";

$(document).ready(
    function ()
    {
        
       function validForm()
       {
        var valid = true;
        var formData = $("#commentForm").serialize();
        values = formData.split("&");
        valuesJSON = {};
        for (i = 0; i<values.length;i++)
        {
            values[i] = values[i].split("=");
            valuesJSON[values[i][0]] = values[i][1];
            if (values[i][0] == "text")
            {
                if (values[i][1] == "")
                {
                    valid = false;
                    message("Text is empty");
                }
                else if (values[i][1].length > 100)
                {
                    valid = false;
                    message("Too many symbols.");
                }
            }
            else if (values[i][0] == "videoId" || values[i][0] == "channelId" || values[i][0] == "userId")
            {
                if (values[i][1] == "")
                {
                    valid = false;
                    message("Site error.");
                }
            }
        }
        return [valid,valuesJSON];
       }
       function setCommentSubmit()
       {
            $("#commentForm").submit(
                function ()
                {
                    if (validForm()[0])
                        $.ajax(
                            {
                                type: "POST",
                                url: "engine/actions/comment.php", 
                                data: validForm()[1],
                                cache: false,
                                success: function (newcomments)
                                    {
                                        $("#Comments").html(newcomments);
                                        message("Ok");
                                        setCommentSubmit();
                                    }
                            }
                        );
                    return false;
                }
            );
       }
       setCommentSubmit();
       function message(text)
       {
            $(".errormsg").remove();
           if (text != "Ok")                
                $("#commentForm").html($("#commentForm").html() + "<p class='errormsg' style='color: red;'>" + text + "</p>");                
       }
    }
);