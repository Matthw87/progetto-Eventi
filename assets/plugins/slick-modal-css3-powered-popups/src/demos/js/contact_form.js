// Slick Modals - Contact Form
var sm_contactForm = function (sel) {
    var name_pattern   = /^[a-zA-Z\s]+$/;
    var email_pattern  = /(.+)@(.+){2,}\.(.+){2,}/;
    var number_pattern = /^-?\d+\.?\d*$/;

    var $sel           = $(sel);
    var $form          = $sel.find('form');
    var $name_input    = $form.find('.name');
    var $email_input   = $form.find('.email');
    var $phone_input   = $form.find('.phone');
    var $message_input = $form.find('.message');
    var $errorMsg       = $form.find('.errorMsg');

    $form.on('submit', function(e) {
        e.preventDefault();

        if (!name_pattern.test($name_input.val())) {
            $errorMsg.text('Please tell us your name');
        } else if (!email_pattern.test($email_input.val())) {
            $errorMsg.text('Please use valid email address');
        } else if (!number_pattern.test($phone_input.val())) {
            $errorMsg.text('A valid phone number is required');
        } else if (!$message_input.val()) {
            $errorMsg.text('Message is required');
        } else {
            $.ajax({
                type: 'POST',
                url: 'yourScript.php', // Insert the path of your mail script
                data: $(this).serialize(),
                success: function() {
                    var $title        = $form.parent().find('.title');
                    var $p            = $form.parent().find('p');
                    var $close        = $form.parent().find('.close');
                    var titleInitText = $title.text();
                    var pInitText     = $p.text();
                    var closeInitText = $close.text();

                    $errorMsg.text('');
                    $title.text('Thank you for your submittion');
                    $p.text('We will be with you soon.');
                    $close.text('Close the form window').css('margin-top', '20px');
                    $form.slideUp();

                    setTimeout(function(){
                        $sel.SlickModals('closePopup');
                        setTimeout(function () {
                            $title.text(titleInitText);
                            $p.text(pInitText);
                            $close.text(closeInitText);
                            $form.slideDown();
                        }, 2000);
                    }, 4000); // Auto close timeout in milliseconds
                },
                error: function() {
                    $errorMsg.text('Something went wrong. Please try again.');
                }
            });
        }
    })
};
