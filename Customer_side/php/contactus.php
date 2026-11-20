<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/fonts.css">
    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../css/contactus.css">

</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

    <div class="contact-wrapper">

        <div class="contact-section">

            <h1 class="contact-title">CONTACT US</h1>

            <div class="contact-container">

                <div class="contact-info">

                    <div class="contact-item">
                        <i class='bx bx-map'></i>
                        <p>Quezon City, Philippines</p>
                    </div>

                    <div class="contact-item">
                        <i class='bx bx-phone'></i>
                        <p>+63 912 345 6789</p>
                    </div>

                    <div class="contact-item">
                        <i class='bx bx-envelope'></i>
                        <p>fizzandbits@gmail.com</p>
                    </div>

                    <div class="contact-item">
                        <i class='bx bx-time'></i>
                        <p>Mon - Fri : 9:00 AM - 6:00 PM</p>
                    </div>

                </div>

                <div class="contact-form">

                    <form>

                        <label>Name</label>
                        <input type="text" placeholder="Enter your name" required>

                        <label>Email</label>
                        <input type="email" placeholder="Enter your email" required>

                        <label>Message</label>
                        <textarea rows="5" placeholder="Write your message..." required></textarea>

                        <button type="submit">Send Message</button>

                    </form>

                </div>

            </div>

            <p class="contact-description">
                Have questions, feedback, or suggestions about <strong>Fizz & Bits</strong>?
                We'd love to hear from you! Whether it's about our menu, your order,
                or collaboration opportunities, feel free to reach out using the form
                above or through our contact details.
            </p>

        </div>

    </div>

</div>

    <?php include 'footer.php'; ?>

</body>

</html>