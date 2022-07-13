"use sctrict";

function createDialogWindow ()
{
    $("body").append("<div id='DialogBackground'><div id='dialogWindow'><div class='head'><button>x</button></div><div class='body'></div></div></div>");
    $("#DialogBackground, #dialogWindow .head button").click(
        function (e)
        {
            if (e.target == this)
                closeDialogWindow();
        }
    );
}
function closeDialogWindow()
{
    $("#DialogBackground").remove();
}