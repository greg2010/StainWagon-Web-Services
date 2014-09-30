var charReady;
var passReady;
var repPassReady;

function SendRequest(toPut, alertPlace, prefix){
    $.ajax({
        type: "POST",
        url: "getChars.php",
        data: "keyID="+$('input[role=keyID-'+prefix+']').val()+"&vCode="+$('input[role=vCode-'+prefix+']').val(),
        datatype: 'json',
        success: function(json){
            parseApiResult(json, toPut, alertPlace);
        }
    });
}

function parseApiResult(json, toPut, alertPlace) {
    if (json.status !== 0) {
        $('#chars').empty();
        $('#chars').attr('hidden', 1);
        $('div[role="'+alertPlace+'"]').empty();
        $('div[role="'+alertPlace+'"]').removeAttr('hidden').text("API server has responed with an error: "+json.message);
    } else {
        $('div[role="'+alertPlace+'"]').empty();
        $('div[role="'+alertPlace+'"]').attr('hidden', 1);
        $(toPut).empty();
        $('#chars').removeAttr('hidden');
        var i = 0;
        $.each(json, function(i, chars) {
            if (typeof(chars) === "object") {
                var idName = 'b' + i;
                $(toPut).append($('<input>').attr('type', 'radio').attr('value', chars.characterName).attr('class', 'r_button').attr('name', 'login').attr('id', idName));
                if (chars.valid === 1) {
                    var id = "success";
                    var className = "glyphicon glyphicon-ok";
                } else {
                    var id = "fail";
                    var className = "glyphicon glyphicon-remove";
                }
                $(':radio[value="'+chars.characterName+'"]').after(function() {
                    var label = $("<label>");
                    $(label).attr('for', idName).attr('id', id).text(chars.characterName).append(function() {
                        var span = $("<span>");
                        $(span).attr('class', className);
                        return $(span);
                    });
                    return $(label)
                });
                i++;
            }
        });
    }
    window.charReady = 1;
    
}

$(document).ready(function() {

    $('#email').blur(function() {
        if($(this).val() !== '') {
            var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
            if(pattern.test($(this).val())){
                $(this).css({'border' : '1px solid #569b44'});
                $('div[role="alert-email"]').attr('hidden', 1);
            } else {
                $(this).css({'border' : '1px solid #ff0000'});
                $('div[role="alert-email"]').removeAttr('hidden').text('Your e-mail is not valid!');
            }
        } else {
            // Поле email пустое, выводим предупреждающее сообщение
            $(this).css({'border' : ''});
            $('div[role="alert-email"]').attr('hidden', 1);
        }
    });
    var passValid;
    var validColor = "#458B00";
    var validBorder = "1px solid #458B00";
    var invalidColor = "#FF4500";
    var invalidBorder = "1px solid #FF4500";
        $('#password').on("input", (function() {
        if($(this).val() !== '') {

            var pwd = $(this).val();
            var count = pwd.length;
            var countValid;
            var numberValid;
            var lowerValid;
            var upperValid;

            if(count > 7){
                $('#length').css({'color' : validColor});
                countValid = 1;
            } else {
                $('#length').css({'color' : invalidColor});
                countValid = 0;
            }
            if(!/\d/.test(pwd)){
                $('#numbers').css({'color' : invalidColor});
                numberValid = 0;
            } else {
                $('#numbers').css({'color' : validColor});
                numberValid = 1;
            }
            if(!/[a-z]/.test(pwd)){
                $('#lowerCase').css({'color' : invalidColor});
                lowerValid = 0;
            } else {
                $('#lowerCase').css({'color' : validColor});
                lowerValid = 1;
            }
            if(!/[A-Z]/.test(pwd)){
                $('#upperCase').css({'color' : invalidColor});
                upperValid = 0;
            } else {
                $('#upperCase').css({'color' : validColor});
                upperValid = 1;
            }
            if (numberValid === 1 && lowerValid === 1 && upperValid === 1 && countValid === 1) {
                passValid = 1;
                window.passReady = 1;
                $(this).css({'border' : validBorder});
            } else {
                passValid = 0;
                window.passReady = 0;
                $(this).css({'border' : invalidBorder});
            }
        }
    }));
    $('#password-repeat').blur(function() {
        if (passValid === 1) {
            if($(this).val() !== $('#password').val()) {
                $(this).css({'border' : invalidBorder});  
                $('div[role="alert-password-repeat"]').removeAttr('hidden').text('Passwords don\'t match!');
                window.repPassReady = 0;
            } else {
                $(this).css({'border' : validBorder});
                window.repPassReady = 1;
                $('div[role="alert-password-repeat"]').attr('hidden', 1).text('');
            }
        }
    });
    
function formCheck() {
    if(window.charReady === 1 && window.passReady === 1 && window.repPassReady === 1) {//we want it to match
        setTimeout(formCheck, 50);
        $('#submit').removeAttr('disabled');
    } else {
        setTimeout(formCheck, 50);//wait 50 millisecnds then recheck
        $('#submit').removeAttr('disabled');
        $('div[role="alert-password-repeat"]').attr('disabled', 1);
        return;
    }
}

formCheck();


$('#charList').change(function() {
    console.log("CharAction");
    $('div:contains(Please choose eligible charater.)').attr('hidden', 1);
});
});