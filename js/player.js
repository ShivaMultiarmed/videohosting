"use sctrict";

$(document).ready(
    function ()
    {
        var Video, CurTime, Interval;

        function playpause()
        {
            Video = $("#PlayerVideo");
            if ($(this).attr("class") == "paused") {
                $(this).attr("class", "playing");
                $("#PlayButton, #smallPlayButton").html("||");
                Video.trigger("play");
            }
            else {
                $(this).attr("class", "paused");
                $("#PlayButton, #smallPlayButton").html(">");
                Video.trigger("pause");
            }
        }
        $("#MiddleControl").click(playpause);
        $("#PlayerButton").click(playpause);
        $("#smallPlayButton").click(playpause);


        $("#PlayerControls").mouseenter
        (
            function () {
                $(this).css("opacity", "1");
            }
        );
        $("#PlayerControls").mouseleave
        (
            function () {
                $(this).css("opacity", "0");
            }
        );
     
        $("#PlayerVideo").on("loadedmetadata", function (event) {
            Video = $("#PlayerVideo")[0];
            
            Duration = Video.duration;
            hours = Math.floor(Duration / 60 / 60);
            mins = Math.floor((Duration - hours * 60 * 60) / 60);
            secs = Math.floor(Duration - mins * 60);
            $("#durTime").html(hours + ":" + mins + ":" + secs);
            
            
            // display cur time 
            Interval = setInterval(
                function () {
                    Video = $("#PlayerVideo")[0];
                    CurTime = Video.currentTime;
                    hours = Math.floor(CurTime / 60 / 60);
                    mins = Math.floor((CurTime - hours * 60 * 60) / 60);
                    secs = Math.floor(CurTime - mins * 60);
                    $("#curTime").html(hours + ":" + mins + ":" + secs);
                    $("#TimeRange").val(CurTime / Duration * 100);
                }, 250
            );
        });     

        $("#TimeRange").on("input",
            function()
            {
                Video = $("#PlayerVideo")[0];
                Video.currentTime = $("#TimeRange").val()/100*Duration;
                CurTime = $("#TimeRange").val()*Duration;  
            }
        );

        function Play()
        {
            Video = $("#PlayerVideo")[0];
            CurTime = Video.currentTime;
            console.log(CurTime);
            hours = Math.floor(CurTime / 60 / 60);
            mins = Math.floor((CurTime - hours * 60 * 60) / 60);
            secs = Math.floor(CurTime - mins * 60);
            $("#curTime").html(hours + ":" + mins + ":" + secs);
            $("#TimeRange").val(CurTime / duration * 100);
        }

        //console.log($("#curTimePos").get());
        //$("#curTimePos").draggable();

        $("#SoundRange").on(
            "input",
            function ()
            {
                Video = $("#PlayerVideo")[0];
                Video.volume = $(this).val()/100;
            }
        );


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
       function setLikeDislikeSubmit()
       {
           
            $('#likeDislikeForm input[type="submit"]').click(
                function ()
                {
                    $('#likeDislikeForm input[name="likeDislike"]').val($(this).attr("name"));
                    $.ajax(
                        {
                            type: "POST",
                            url: "engine/actions/likedislike.php", 
                            data: $("#likeDislikeForm").serialize(),
                            cache: false,
                            success: function (props)
                                {
                                    $("#mainVideoProperties").html(props);
                                    setLikeDislikeSubmit();
                                }
                        }
                    );
                    return false;
                }
            );
       }
       setLikeDislikeSubmit();

       $("#subVideoProperties form input[type='submit']").click(
           function ()
           {
               switch ($(this).attr("name"))
               {
                   case "share":
                        $("#subVideoProperties form input[name='url']").select();
                        document.execCommand("copy");
                   break;
                   case "save":
                        createPlaylistDialog();
                        $("#dialogWindow form input[type='checkbox']").click(
                            function()
                            {
                                console.log("hey");
                                //console.log($("#subVideoProperties form").serialize()+"&playlist="+$(this).val()+"&add="+$(this).attr("checked"));
                                /*$.ajax(
                                    {
                                        cache: false,
                                        data: $("#subVideoProperties form").serialize()+"&playlist="+$(this).val()+"&add="+$(this).attr("checked"),
                                        type: "POST",
                                        url: "engine/actions/playlists.php",
                                        success:
                                            function (res)
                                            {
                                                console.log(res);
                                            }   
                                    }
                                );*/
                            }
                        );
                   break;
               }
               return false;
           }
       );
       function createPlaylistDialog ()
       {
            createDialogWindow();
            $.ajax(
                {
                    cache: false,
                    data: $("#subVideoProperties form").serialize(),
                    type: "POST",
                    url: "engine/actions/playlistdialog.php",
                    success: 
                        function (list)
                        {
                            $("#dialogWindow .body").html(list);
                        }
                }
            );
       }
       
    }
);