<style>
    .popup_box {
        padding: 30px 20px;
    }
    .popup_inner input,
    .popup_inner select {
        margin-bottom: 15px;
        height: 50px;
        font-size: 16px;
        border-radius: 6px;
    }
    .boxed-btn3 {
        background: #bd8f03ff;
        color: white;
        border: none;
        height: 50px;
        font-size: 18px;
        border-radius: 6px;
        transition: background 0.3s ease;
    }
    .boxed-btn3:hover {
        background: #e1b226ff;

        .select-light {
    color: #999; /* Light gray like placeholder */
}

.select-light option {
    color: #000; /* Normal black once selected */
}

/* Optional: prevent placeholder option from being selected */
.select-light option[disabled] {
    display: none;
}

.popup_inner textarea {
    margin-bottom: 15px;
    font-size: 16px;
    border-radius: 6px;
    padding: 10px;
}


    }
</style>


<!-- ✅ Only one form! -->
<form id="test-form" class="white-popup-block mfp-hide" action="contact_process.php" method="POST">
    <div class="popup_box">
        <div class="popup_inner">
            <h3>Send Your Query Here</h3>
            <div class="row">
                
                <div class="col-12">
                    <div class="form-group">
                        <textarea class="form-control w-100" name="message" id="message" cols="30" rows="9"
                            placeholder="Enter Message" required></textarea>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <input class="form-control" name="name" id="name" type="text" placeholder="Enter your name" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <input class="form-control" name="email" id="email" type="email" placeholder="Email" required>
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-group">
                        <input class="form-control" name="phone" id="phone" type="text" placeholder="Phone Number" required>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="col-xl-12 mt-2">
                    <button type="submit" class="boxed-btn3">Send</button>
                    
                </div>
            </div>
            
        </div>
        <!-- Where result will appear -->
        <!-- <div id="form-message"></div> -->

    </div>
</form>
<script>
$(document).ready(function () {
    $('#booking-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'backend/checkAvailability.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                $('#form-message').html(response);
                $('#booking-form')[0].reset();
            },
            error: function () {
                $('#form-message').html('<div class="alert alert-danger mt-3">Error submitting the form. Please try again.</div>');
            }
        });
    });
});
</script>

