<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Dinding Langit</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo.jpeg') }}" />

    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>

    <style>
        .card-container {
            perspective: 1000px; /* Adds depth for the flip effect */
        }

        .card {
            width: 500px;
            height: 500px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-front,
        .card-back {
            width: 100%;
            height: 100%;
            position: absolute;
            backface-visibility: hidden; /* Hides the back of the card */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
        }

        .card-front {
            transform: rotateY(0deg);
        }

        .card-back {
            transform: rotateY(180deg);
        }

        /* Flip Effect */
        .card.flipped {
            transform: rotateY(180deg);
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh">
        <div class="card-container">
            <div class="card" id="card">
                <!-- Sign-In Form -->
                <div class="card-front">
                    <div class="d-flex justify-content-center align-items-center mb-4 w-25">
                        <img class="img-fluid me-3" style="height: 60px" src="{{ asset('assets/img/logo.jpeg') }}" alt="" srcset="">
                        <h3 class="mb-0">Sign In</h3>
                    </div>
                    <form class="w-100" action="/signin" method="post">
                        @csrf
                        <input class="w-100 form-control mb-3" name="username" type="text" placeholder="Username" required>
                        <input class="w-100 form-control mb-3" name="password" type="password" placeholder="Password" required>
                        <button class="btn btn-dark w-100" type="submit">Sign In</button>
                    </form>
                    {{-- <span class="mt-3">Don't have an account? <a href="#" onclick="flipCard()">Sign Up</a></span> --}}
                </div>

                <!-- Sign-Up Form -->
                <div class="card-back">
                    <div class="d-flex justify-content-center align-items-center mb-4 w-25">
                        <img class="img-fluid me-3" style="height: 60px" src="{{ asset('assets/img/logo.jpeg') }}" alt="" srcset="">
                        <h3 class="mb-0">Sign Up</h3>
                    </div>
                    <form class="w-100" action="/signup" method="post">
                        @csrf
                        <input class="w-100 form-control mb-3" name="name" type="text" placeholder="Full Name" required>
                        <input class="w-100 form-control mb-3" name="username" type="text" placeholder="Username" required>
                        <input class="w-100 form-control mb-3" name="email" type="email" placeholder="Email" required>
                        <input class="w-100 form-control mb-3" name="password" type="password" placeholder="Password" required>
                        <input class="w-100 form-control mb-3" name="password_confirmation" type="password" placeholder="Confirm Password" required>
                        <button class="btn btn-dark w-100" type="submit">Sign Up</button>
                    </form>
                    <span class="mt-3">Already have an account? <a href="#" onclick="flipCard()">Sign In</a></span>
                </div>
            </div>
        </div>
    </div>

    <script>
        function flipCard() {
            const card = document.getElementById('card');
            card.classList.toggle('flipped');
        }
    </script>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
</body>
</html>
