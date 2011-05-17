/*
 *  Auteur:     Stefan Meier
 *  Version:    2010.11.07
 */

function disableForm() {
    $("#eventform :input").each(function() {
        $(this).attr('disabled', true);
    });
    $(".ui-dialog-buttonpane").remove();
}

function wholeDay() {
    if ($('#whole_day').is(':checked')) {

        $("#start").hide();
        $("#start_hour option[text=00]").attr('selected', true);
        $("#start_min option[text=00]").attr('selected', true);

        $("#end").hide();
        $("#end_hour option[text=00]").attr('selected', true);
        $("#end_min option[text=00]").attr('selected', true);

    }
    else {
        $("#start").show();
        $("#end").show();

    }
}

function updateConfirm() {
    $("#dialog-confirm-repeat").html("<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>Souhaitez-vous vraiment mettre à jour cet événement ainsi que toutes ses occurences futures?</p>");
    $("#dialog-confirm-repeat").dialog({
        resizable: false,
        height:130,
        width: 550,
        modal: true,
        buttons: {
            "Annuler": function() {
                $("#modifyall").val("");
                $( this ).dialog( "close" );
            },
            "Oui": function() {
                $("#modifyall").val("true");
                sendForm('edit');
                $( this ).dialog( "close" );
            }
        }
    });
}

function confirmChanges(action) {
    $("#dialog-confirm-repeat").html("<p><span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span>Souhaitez-vous supprimer cet événement ainsi que toutes ses occurences futures ou uniquement celle sélectionnée?</p>");
    $("#dialog-confirm-repeat").dialog({
        resizable: false,
        height:130,
        width: 550,
        modal: true,
        buttons: {
            "Annuler": function() {
                $("#modifyall").val("");
                $( this ).dialog( "close" );
            },
            "Tous les événements futures": function() {
                $("#modifyall").val("true");
                sendForm(action);
                $( this ).dialog( "close" );
            },
            "Seulement cette occurence": function() {
                $("#modifyall").val("false");
                sendForm(action);
                $( this ).dialog( "close" );
            }
        }
    });
}

function alerteIndisponibilite() {
   
    $("#dialog-alerte-indisponibilite").dialog({
        resizable: false,
        width: 350,
        modal: true,
        buttons: {
            "OK": function() {
                $( this ).dialog( "close" );
            }/*,
            "Insérer évéenemts disponibles": function() {
                sendForm('insert-available');
                $( this ).dialog( "close" );
            }*/
        }
    });
}

function repeatSelector() {
    if ($("#repeat :selected").val() == 'n') {
        $("#repeat_date").hide();
        $("#repeat_end").removeClass('required');
    }
    else {
        $("#repeat_date").show();
        $("#repeat_end").addClass('required');
    }
}

function eventUI(lang) {
    $.ajax({
        type: "GET",
        url: "xml/lang/" + lang + "/event.xml",
        dataType: "xml",
        success: function(xml) {

            var event = $('event', xml);

            $('.dialog-title').html(event.children($('.dialog-title').attr('id')).text());

            $('#event-user').html(event.children('user').text());
            $('#event-title').html(event.children('title').text());
            $('#event-date').html(event.children('date').text());
            $('#event-whole-day').html(event.children('whole-day').text());
            $('#event-start').html(event.children('start').text());
            $('#event-end').html(event.children('end').text());

            var repeat = event.children('repeat');

            $('#event-repeat').html(repeat.children('title').text());
            $('#repeat-n').html(repeat.children('never').text());
            $('#repeat-d').html(repeat.children('daily').text());
            $('#repeat-w').html(repeat.children('weekly').text());
            $('#repeat-2w').html(repeat.children('half-monthly').text());
            $('#repeat-m').html(repeat.children('monthly').text());
            $('#repeat-y').html(repeat.children('yearly').text());
            $('#repeat-until').html(repeat.children('until').text());


            $('#event-description').html(event.children('description').text());


            $('#save').html('<span class=\"ui-button-text\">' + event.children('save').text() + '</span>');
            $('#delete').html('<span class=\"ui-button-text\">' + event.children('delete').text() + '</span>');
            $('#cancel').html('<span class=\"ui-button-text\">' + event.children('cancel').text() + '</span>');


            var messages = event.children('messages');

            $('#message-repeat').html(messages.children('repeat-info').text());
            $('#message-other-user').html(messages.children('other-user').text());

            var errors = messages.children('error');

            errormsg['time'] = errors.children('time').text();
            errormsg['unavailable'] = errors.children('unavailable').text();
            checkmsg = '<span style=\"color:red\">' + messages.children('check').text() + '</span>';
        }
    });
}

$(document).ready(function() {

    eventUI(lang);

    $('#name').focus();

    if ($("#edate").val() == '') {

        $("#edate").val(caldate);
    }
    if ($("#repeat_end").val() == '') {
        $("#repeat_end").val(caldate);
    }
    /*$("#edate").change(function() {

        edate = $("#edate").val();
        $("#repeat_end").val(edate);
    });*/

    $("#start_hour").change(function() {
        start = $("#start_hour").val();
        
        $("#end_hour").val("23");
        if (start < 23) {
            value =  parseInt(start)+ 1;
            if (start < 10) {
                value = '0' + value;
            }
            $("#end_hour").val(value);
        }
        

        
    });

    $("#repeat_date").hide();
    // Datepicker
    $(".datepicker").datepicker({
        dayNamesMin: [dayShortNames[6], dayShortNames[0], dayShortNames[1], dayShortNames[2], dayShortNames[3], dayShortNames[4], dayShortNames[5]],
        monthNames: [monthNames[0],monthNames[1],monthNames[2],monthNames[3],monthNames[4],monthNames[5],monthNames[6],monthNames[7],monthNames[8],monthNames[9],monthNames[10],monthNames[11]],
        firstDay: 1,
        maxDate: (new Date().getFullYear() + maxYearOffset) + '-12-31',
        minDate: new Date().getFullYear() + '-1-1',
        dateFormat: 'yy-mm-dd'
    });

    wholeDay();

    $('#whole_day').change(function() {
        wholeDay();
    });
    repeatSelector();
    $('#repeat').change(function() {
        repeatSelector();
    });
});
