// Slick Modals - Events Calendar
var sm_eventsCalendar = function (sel) {

    var exist = window.sm_eventsCalendarExist || 0;

    if (!exist) {

        // Define your events below (skip comma after closing the bracket of the last event)
        var events = [
            {
                Date: new Date('12/28/2018'),
                Time: '11:00 AM - 13:00 PM',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('01/15/2019'),
                Time: 'all day event',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('04/03/2019'),
                Time: '9:00 AM - 10:00 AM',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('08/13/2019'),
                Time: '8:00 AM - 1:00 PM',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('09/14/2019'),
                Time: 'all day event',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('10/11/2019'),
                Time: '9:00 AM - 10:00 AM',
                Title: 'Lorem ipsum dolor',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            },
            {
                Date: new Date('03/24/2020'),
                Time: '9:00 AM - 10:00 AM',
                Title: 'Last event lorem ipsum',
                Summary: 'Lorem ipsum dolor sit amet, nec placerat mediocrem ei. Unum illum his ex, ex vero choro.'
            }
        ];
        var $sel          = $(sel);
        var $eventWrap    = $sel.find('.events');
        var $calendar     = $sel.find('.calendar');
        var $eventDate    = $eventWrap.find('.date');
        var $eventTitle   = $eventWrap.find('.title');
        var $eventSummary = $eventWrap.find('.summary');
        var $closeEvent   = $eventWrap.find('.closeEvent');

        function formatDate (val) {
            return formated = val.toString().split(' ').slice(0, 4).join(' ');
        }

        $calendar.datepicker({
            showOtherMonths: true,
            beforeShowDay: function(date) {
                var result = [true, '', null];
                var matching = $.grep(events, function (event) {
                    return event.Date.valueOf() === date.valueOf();
                });
                if (matching.length) result = [true, 'highlight', null];
                return result;
            },
            onSelect: function(dateText) {
                var date;
                var selectedDate = new Date(dateText);
                var i = 0;
                var event = null;

                while (i < events.length && !event) {
                    date = events[i].Date;
                    if (selectedDate.valueOf() === date.valueOf()) event = events[i];
                    i++;
                }

                if (event) {
                    formatDate(event.Date);

                    $eventDate.text(formated + ' (' + event.Time + ')');
                    $eventTitle.text(event.Title);
                    $eventSummary.text(event.Summary);

                    if ($($eventWrap).css('display') === 'none') {
                        setTimeout(function() {
                            $eventWrap.slideDown('fast');
                        }, 100);
                    }

                    $closeEvent.on('click', function () {
                        $eventWrap.slideUp('fast');
                    });
                }
            }
        });

        return window.sm_eventsCalendarExist = 1;
    }

};