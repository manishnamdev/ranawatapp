<?php
session_start();
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">

            <h5 class="text-center fw-bold mb-3">
                नियम एवं शर्तें
            </h5>

<ul class="small">
    <li>आपका रांकावत समाज के 35 गाँवों से सम्बंधित परिवार में जन्म सिद्ध अधिकार होना आवश्यक है।</li>
    <li>आप द्वारा चुने गए गाँव में सही पुष्टि होने पर ही सदस्यता दी जाएगी।</li>
    <li>असत्यापित जानकारी देने पर सदैव के लिए आपकी सदस्यता निरस्त की जाएगी।</li>
    <li>समाज की सदस्यता सूची का दुर्भावना से दुरुपयोग किए जाने पर उसकी सम्पूर्ण जिम्मेदारी आपकी स्वयं की होगी। समाज अथवा सम्बंधित व्यक्ति/परिवार आप पर सामाजिक अथवा कानूनी कार्यवाही करने के लिए स्वतंत्र होंगे।</li>
    <li>समाज की सदस्यता सूची में नाम अंकित होने मात्र से आप संस्था के चुनावों में मताधिकारी सदस्य नहीं माने जाएँगे।</li>
</ul>

            <form method="post" action="rules_process.php">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="agree" id="agree" required>
                    <label class="form-check-label fw-semibold" for="agree">
                        मैं उपरोक्त सभी नियमों एवं शर्तों से सहमत हूँ
                    </label>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        आगे बढ़ें
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>
