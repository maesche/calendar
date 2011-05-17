/*
 *  Auteur:     Stefan Meier
 *  Version:    2010.11.07
 */
// Initialisation des variables

var lang = 'en';

var dialogTitle = '';
var caldate = '';
var view = '';
var eventMode = 'add';
var eventPosX;
var eventPosY;
var eventSalle;
var errormsg = new Array();
var checkmsg = "";
var month = new Date().getMonth() + 1;
var year = new Date().getFullYear();
var room = 13;
var building = 27;
var monthNames;
var monthShortNames;
var dayNames;
var dayShortNames;
var maxYearOffset = 5; //date max. dans le futur

function init() {
    /*Fonction pour réécrire le buttonpane afin de pouvoir
     *définir l'id, la classe et le titre. Dévleoppée par
     * http://blog.mynotiz.de/programmieren/jquery-ui-dialog-buttons-anpassen-945/
     *
     *Utilisation:
     *
     *          "MonButton" : $.extend(function() {
                    actions à faire
                }, {
                    classes : 'maclasse',
                    title : 'montittre',
                    id : 'monid'
                }),
     */
    (function() {
        var dialogPrototype = $.ui.dialog.prototype;
        var originalButtons = dialogPrototype._createButtons;
        dialogPrototype._createButtons = function(buttons) {

            originalButtons.apply(this, arguments);

            var $buttons = this.element.siblings('.ui-dialog-buttonpane').find('button');

            var i = 0;
            for ( var label in buttons) {
                var button = buttons[label];
                var $button = $buttons.eq(i);

                if (button.title) {
                    $button.attr('title', button.title);
                }

                if (button.classes) {
                    $button.addClass(button.classes);
                }

                if (button.id) {
                    $button.attr('id', button.id);
                }

                i += 1;
            }
        }

    })();
}

function newEvent(caldate) {
    this.dialogTitle = '<span class=\"dialog-title\" id=\"new\"></span>';
    this.eventMode = 'add';
    this.caldate = caldate;
}

function editEvent(posX, posY, caldate) {
    this.dialogTitle = '<span class=\"dialog-title\" id=\"edit\"></span>';
    this.eventMode = 'edit';
    this.eventPosX = posX;
    this.eventPosY = posY;
    this.caldate = caldate;
}

function appUI() {
    if ($.cookie("lang") != null) {
        this.lang = $.cookie("lang");
    }

    $('.language').css('font-weight', 'normal');
    $('#' + lang).css('font-weight', 'bold');



    $.ajax({
        type: "GET",
        url: "xml/lang/" + lang + "/application.xml",
        dataType: "xml",
        success: function(xml) {

            var application = $('application', xml);

            //$('#page-title').html($('application>title', xml).text());

            $('#page-title').html(application.children('page-title').text());

            $('#information-title').html(application.children('information-title').text());
            $('#calendar-choice').html(application.children('calendar-choice').text());
            $('#go-to').html(application.children('go-to').text());

            var months = application.children('months');
            monthNames = new Array();
            monthShortNames = new Array();

            var count = 1;
            $('#month').html('');
            months.children('month').each(function(){
                var mName = $(this).children('full').text();
                monthNames.push(mName);

                monthShortNames.push($(this).children('short').text())

                $('#month').append('<option value=\"' + count + '\">' + mName + '</option>');
                count++;
            });


            dayNames = new Array();
            dayShortNames = new Array();

            count = 1;
            var days = application.children('days');

            days.children('day').each(function(){
                var dName = $(this).children('full').text();
                dayNames.push(dName);
                dayShortNames.push($(this).children('short').text())
                count++;
            });

            buildings();
            calendar();
        }
    });

}

function buildings() {
    if ($.cookie("building") != null) {
        building = $.cookie("building");
    }

    $.get("views/buildings.php", {
        "id": building
    },
    function(data){
        $('#rooms').html(data);
        $('#buildings [value=' + building + ']').attr('selected', true);
        $('#room [value=' + room + ']').attr('selected', true);
    });
}

function calendar() {
    if ($.cookie("room") != null) {
        room = $.cookie("room");

    }
    if ($.cookie("year") != null) {
        year = $.cookie("year");
    }
    if ($.cookie("month") != null) {
        month = $.cookie("month");
    }
    $.get("views/calendar.php", {
        "view": view,
        "room": room,
        "year" : year,
        "month" : month
    },
    function(data){
        $('#calendar').html(data);

        //importation du code html plutôt que de la balise
        $('#roomName').html($('#cal_roomName').html());
        $('#roomDescription').html($('#cal_roomDescription').html());
        $('#cal_roomName').remove();
        $('#cal_roomDescription').remove();

        for (i = 0; i < 7; i++) {

            $("#day" + (i+1)).html(dayNames[i]);
        }

    });
    $("#monthname").html(monthNames[(parseInt(month) - 1)]);
    $("#yearName").html(year);

    if (lang == 'ja') {
        $("#yearName").append(' 年');
    }


    $("#month").val(month);
    $("#year").html('');

    var y = new Date().getFullYear();
    for (i = 0; i <= maxYearOffset; i++) {
        $('#year').append('<option value=\"' + (y + i) + '\">' + (y + i) + '</option>');
    }

    $("#year").val(year);
}

function browserLang() {
    var lct="fr";
    if (navigator.language) {
        lct=navigator.language.toLowerCase().substring(0, 2);
    } else if (navigator.userLanguage) {
        lct=navigator.userLanguage.toLowerCase().substring(0, 2);
    } else if (navigator.userAgent.indexOf("[")!=-1) {
        var debut=navigator.userAgent.indexOf("[");
        var fin=navigator.userAgent.indexOf("]");
        lct=navigator.userAgent.substring(debut+1, fin).toLowerCase();
    }
    return lct;
}

//Initialisation de JQuery
$(document).ready(function() {
  
    if (browserLang() != null && browserLang() != 'undefined' && browserLang() != "") {
        lang = browserLang();
        $.cookie("lang", lang);
    }
    //$.cookie("lang", lang);
    appUI();


    $(".language").click(function() {

        lang = $(this).attr('id');
        $.cookie("lang", lang);
        appUI();
    });


    $("#prev").click(function() {
        y = parseInt(year);
        m = parseInt(month);

        if(!((new Date().getFullYear() == year) && (month == 1)))
        {

            prevYear = (m != 1) ? y : (y - 1);
            prevMonth = (m == 1) ? 12 : (m - 1);

            year = prevYear;
            month = prevMonth;

            $.cookie("year", year);
            $.cookie("month", month);

            calendar();
        }
    });

    $("#next").click(function() {

        y = parseInt(year);
        m = parseInt(month);

        if(!(((new Date().getFullYear() + maxYearOffset) == year) && (month == 12)))
        {
            nextYear = (m != 12) ? y : (y + 1);
            nextMonth = (m == 12) ? 1 : (m + 1);

            year = nextYear;
            month = nextMonth;
            $.cookie("year", year);
            $.cookie("month", month);
            calendar();
        }
    });

});