$(document).ready(
    function ()
    {
        $("#ChannelProfile input[type='submit'], .channelprofile input[type='submit']").click(
            function ()
            {
                var thisbtn = $(this);
                $.ajax(
                    {
                        cache: false,
                        data: $(this).parent().serialize(),
                        url: "engine/actions/subscribe.php",
                        type: "POST",
                        success: 
                            function (result)
                            {
                                thisbtn.attr("class",result);
                                thisbtn.val((result == "yes") ? "subscribed" : "subscribe");
                            }
                    }
                );
                return false;
            }
        );
    }
);