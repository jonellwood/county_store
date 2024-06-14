<?php


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script>
        let y, z; // Declare y and z in a higher scope

        // Function to generate random numbers and set the initial question
        function generateRandomNumbers() {
            console.log(
                'The icons used on the menu in the chatbot are all from the https://thenounproject.com. This is the best way I can think of to attribute them at the moment. But they are super awesome and I highly recommend checking them out. And if by some chance someone from the Noun Project is reading this.. I love your site and what you all do.'
            )
            y = Math.floor(Math.random() * 6); // Generates a random number between 0 and 5
            z = Math.floor(Math.random() * 6);
            // console.log("y is: " + y);
            // console.log("z is: " + z);
            const html = "What is " + y + " + " + z + "?";
            document.getElementById('question').innerHTML = html;
        }

        function checkAnswer() {
            const userAnswer = parseInt(document.getElementById('userAnswer').value, 10);
            // console.log('userAnswer', userAnswer);
            // console.log('answer is: ', y + z);
            if (userAnswer === y + z) {
                // alert('Good Job!');
                enableBtn();
            } else {
                alert('Sorry, that is incorrect');
                location.reload();
            }
        }

        // Initialize the game with random numbers on page load
        window.onload = function() {
            generateRandomNumbers();
        };
    </script>
    <script type="text/javascript">
        (function(w, d, x, id) {
            s = d.createElement('script');
            s.src = 'https://dtn7rvxwwlhud.cloudfront.net/amazon-connect-chat-interface-client.js';
            s.async = 1;
            s.id = id;
            d.getElementsByTagName('head')[0].appendChild(s);
            w[x] = w[x] || function() {
                (w[x].ac = w[x].ac || []).push(arguments)
            };
        })(window, document, 'amazon_connect', '6eb9ec08-ada7-40d2-8800-24d8abfd1fc1');
        amazon_connect('styles', {
            openChat: {
                color: '#ffffff',
                backgroundColor: '#123456'
            },
            closeChat: {
                color: '#ffffff',
                backgroundColor: '#123456'
            }
        });
        amazon_connect('snippetId',
            'QVFJREFIakZhMVo2ZGZmSXpGSnpJS2lYakthYVBxMmJIU0ZPbnhET3AyalJDV1F3UWdHYVJiM2YvSEVmMzlZOG04TXhnU1NUQUFBQWJqQnNCZ2txaGtpRzl3MEJCd2FnWHpCZEFnRUFNRmdHQ1NxR1NJYjNEUUVIQVRBZUJnbGdoa2dCWlFNRUFTNHdFUVFNd3AyODJZQndtdXlucWY3dkFnRVFnQ3VjK1pGZVR3UmdXMnphTTRRS0tkbG9BZVd2dFBUL2o1S1pKZUpGY3pweG5hWndWblBKM2dqWC9pU3Q6OjhVNlAvb3o1RFJoazZvQWl2akphK2RLcXNzUFRjN2JIZEZQRHI1TkZLNFdnWWh3VjZidFlRbGVWV09lenV0NHArd0taY2U2MnFQNGo4WG10WTRKQjdMeGNkMUJacXFCaXFsYnVRZldkRmxkT1dUNDR3dmxlZkh1bW94VW1tbGVTWmNPWHdqc1FiSkRTODltTVY4M3E3ZjNzTytyL2hkQT0='
        );
        amazon_connect('supportedMessagingContentTypes', ['text/plain', 'text/markdown']);
    </script>
    <!-- <script type="text/javascript">
        (function(w, d, x, id) {
            s = d.createElement('script');
            s.src = 'https://dtn7rvxwwlhud.cloudfront.net/amazon-connect-chat-interface-client.js';
            s.async = 1;
            s.id = id;
            d.getElementsByTagName('head')[0].appendChild(s);
            w[x] = w[x] || function() {
                (w[x].ac = w[x].ac || []).push(arguments)
            };
        })(window, document, 'amazon_connect', 'da17ec38-537e-461c-8fa4-d3d2bd9cb18e');
        amazon_connect('styles', {
            iconType: 'CHAT',
            openChat: {
                color: '#ffffff',
                backgroundColor: '#123456'
            },
            closeChat: {
                color: '#ffffff',
                backgroundColor: '#123456'
            }
        });
        amazon_connect('snippetId',
            'QVFJREFIaWFZYXRVSlpIekdkUUg5YXhZenVQMktKRXNIWTVFQWpBYVErTEdzRnpvZHdGeUNEOHJEMzF3WlRFQ0NHLzhldmZmQUFBQWJqQnNCZ2txaGtpRzl3MEJCd2FnWHpCZEFnRUFNRmdHQ1NxR1NJYjNEUUVIQVRBZUJnbGdoa2dCWlFNRUFTNHdFUVFNcklyNExkZG9SN1VUUW5jd0FnRVFnQ3Uwb3EwTFM5SmhhV3VjaEt2SjduUGpDZ3NKaHpNN0hnRHJ3amhQd1R2Qk5TRTl4a3FrZHZwQnQ1bmY6OlJwNCtKd0Z2aDhMRVE4dktVQVpWQ0x3MFdudUgvZ0ZyVzJNaWh2OXJ1VFBtdHoyYUNTVHlSWm50WGZnVkNZQ2FqYkgzYWNHUXdHTGxMb2hBdnNBcGI0cEszdUowY3N2cnlpcXpFT01MQlhsZlByM2FCS3RIWFAwRjBJdUhtclZ4KzFrc1p4QnpuSUovbG1DeHh5WVFWQjZjaXQyWUtYWT0='
        );
        amazon_connect('supportedMessagingContentTypes', ['text/plain', 'text/markdown',
            'application/vnd.amazonaws.connect.message.interactive',
            'application/vnd.amazonaws.connect.message.interactive.response'
        ]);
    </script> -->

</head>

<body>
    <img src="dept_logos/bc-seal.png" alt="bcg logo">
    <h1>BerkeleyEngage</h1>
    <h3>NOTICE: This page and resources are only for development.</h3>
    <div class="notice-container">
        <div class="cool-stuff">
            <ul>
                <li><?php echo $_SERVER['GATEWAY_INTERFACE'] ?></li>
                <li><?php echo $_SERVER['SERVER_ADDR'] ?></li>
                <li><?php echo $_SERVER['SERVER_NAME'] ?></li>
                <li><?php echo $_SERVER['SERVER_SOFTWARE'] ?></li>
                <li><?php echo $_SERVER['SERVER_PROTOCOL'] ?></li>
                <li><?php echo $_SERVER['SERVER_ADMIN'] ?></li>
                <li><?php echo $_SERVER['SERVER_PORT'] ?></li>
                <li><?php echo $_SERVER['REQUEST_METHOD'] ?></li>
                <li><?php echo $_SERVER['REQUEST_TIME'] ?></li>
                <li><?php echo $_SERVER['DOCUMENT_ROOT'] ?></li>
                <li><?php echo $_SERVER['REMOTE_ADDR'] ?></li>
                <!-- <li></?php echo $_SERVER['REMOTE_HOST'] ?></li> -->
                <li><?php echo $_SERVER['REMOTE_PORT'] ?></li>
                <!-- <li></?php echo $_SERVER['REMOTE_USER'] ?></li> -->
            </ul>
        </div>

        <!-- <div>
            <p>First let's make sure YOU are not a robot. Please answer the question below.</p>
            <p id='question'></p>
            <p>Fill in your answer </p>
            <input type="text" id="userAnswer">
            <button type="button" onclick="checkAnswer()">Submit Answer</button>
        </div> -->

    </div>

</body>

</html>

<script>
    function enableBtn() {
        var chatBox = document.getElementById('amazon-connect-chat-widget');
        var chatBtn = chatBox.querySelector("button")
        // console.log(chatBtn, 'pressed')
        // chatBox.style.visibility = "visible";
        chatBtn.click();
    }

    // function disableBtn() {
    //     var chatBtn = document.getElementById('amazon-connect-chat-widget')
    //     chatBtn.disabled = true
    // }
    // disableBtn();
</script>

<style>
    html {
        /* background-color: #d4dfe7; */
        background-color: #1e242b;
    }

    img {
        width: 10%;
    }

    .notice-container {
        width: 60%;
        margin-top: 5%;
        margin-left: 5%;
    }

    .notice-container p,
    li {
        padding: 5px;
        font-size: medium;
    }


    .show {
        /* height: 810px !important; */
        height: 85vh !important;
        width: 500px !important;
    }

    h1,
    h3 {
        text-align: center;
        color: #cbc8c7;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    #amazon-connect-chat-widget {
        /* visibility: hidden; */
    }

    .cool-stuff {
        text-align: right;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        /* color: #cbc8c7; */
        color: #4e9a06;
    }

    .cool-stuff ul {
        list-style-type: none;
    }
</style>