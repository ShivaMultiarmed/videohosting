"use sctrict";

$(document).ready(
    function ()
    {
        var Video, CurTime, Interval;

        Video = $("#PlayerVideo");

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

        $("#MiddleControl").click(
            function ()
            {
                $("#MiddleControl").focus();
            }
        );
        $(":not(#MiddleControl)").click(
            function ()
            {
                $("#MiddleControl").blur();
            }
        );
        $("#MiddleControl").focus(
            function ()
            {
                // Controls of the video

                $("body").keydown(
                    function (e)
                    {
                        Video = $("#PlayerVideo");

                        //alert(e.code);

                        switch(e.code)
                        {
                            case "ArrowRight":
                                Video[0].currentTime += 5; 
                            break;
                            case "ArrowLeft":
                                Video[0].currentTime -= 5; 
                            break;
                            case "ArrowUp":
                                Video[0].volume += 0.1; 
                                $("#SoundRange").val($("#SoundRange").val()+10);
                            break;
                            case "ArrowDown":
                                Video[0].volume -= 0.1; 
                                $("#SoundRange").val($("#SoundRange").val()-10);
                            break;
                            case "Space":
                                playpause();
                            break;
                        }
                    }
                );
            }
        );        

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
     
        // Display entire and current time

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

        // Change current time via time range (input range)

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
        $("#SpeedSetting ul li").click(
            function ()
            {
                Video = $("#PlayerVideo");
                Video[0].playbackRate = parseFloat($(this).html());
            }
        );

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
                        $("#dialogWindow form input[type='checkbox']").ready(
                            function ()
                            {
                                alert($("#dialogWindow form input[type='checkbox']")); 
                                $("#dialogWindow form input[type='checkbox']").on(
                                    "click",
                                    function()
                                    {
                                        alert("hey");
                                        //console.log("playlist="+$(this).val()+"&add="+$(this).attr("checked"));
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