<div class="mini-top-nav">
    <!-- <form class="form" role="search" data-title="Search" data-intro="Search for orders, by name, id, or products. Coming soon." data-step="2">
        <input class="form-control me-2" type="search" placeholder="To use press ctrl+F" aria-label="Search">
        <button class="button btn-primary" type="submit">Search</button>
    </form> -->
    <div>
        <div class="review-training"><button onclick="deleteCookie('introjs-dontShowAgain')" class="button">Review Training</button>
        </div>
    </div>
    <div class="right-content">
        <!-- <div class="review-training"><button onclick="hideDenied()">Hide Denied</button></div> -->

        <!-- <div class="review-training"><button class="button" popovertarget="feedback-form" popovertargetaction="show">Beta
                Feedback</button></div> -->
        <!-- <div class="settings-cog" data-title="Account Settings" data-intro="Update and manage your user account, assign your staff to specific roles... Coming Soon." data-step="3">
            &#9881;</div> -->
        <div data-title="It's your name! &#127881;" data-intro="Hey Look! Your name. If this is not your name please log in as you!" data-step="4">
            <?php echo $_SESSION['userName'] ?></div>
    </div>
</div>
<div class="feedback-form-holder" id="feedback-form" popover=manual>
    <button class="close-btn" popovertarget="feedback-form" popovertargetaction="hide">
        <span aria-hidden=”true”>❌</span>
        <span class="sr-only">Close</span>
    </button>
    <br>
    <span>
        <h4>Use this form to submit feedback regarding the beta. General questions about an order or product should be
            submitted here <a href="../../support.php" target="_blank"><b><mark class="mark">
                        help.</mark></b></a>
        </h4>
    </span>
    <br>
    <form class="feedback-form" action="beta-feedback-send.php" method="post">
        <label for="feedback-type">Feedback Category</label>
        <select name="feedback-type">
            <option value="Wow! This is so great!">&#127881; Wow! This is so great! &#128588;</option>
            <option value="Missing Feature from last version">&#10166; Missing Feature from last version</option>
            <option value="X is too hard to Find">&#128269; "X" is too hard to Find</option>
            <option value="Why can't I see ...">&#10068; Why can't I see ...</option>
            <option value="New Feature Request">&#128161; New Feature Request</option>
            <option value="Other">&#129445; Other</option>
        </select>
        <br>
        <label for="feedback-textarea">Please let us know your thoughts.</label>
        <textarea name="feedback-textarea" rows="4" cols="50"></textarea>
        <br>
        <label for="feedback-user">Your Name</label>
        <input name="feedback-user" type="text" value="<?php echo $_SESSION['userName'] ?>">
        <label for="feedback-user-email">Email Address</label>
        <input name="feedback-user-email" value="<?php echo $_SESSION['email'] ?>">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<style>
    .mini-top-nav {
        /* position: fixed; */
        /* top: 0; */
        /* left: 0; */
        width: 90%;
        height: 50px;
        box-shadow: 0px 10px 38px -17px rgba(59, 54, 59, 1);
        /* margin-left: 150px; */
        /* margin-right: 20px; */
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        padding: 10px;
        align-items: center;
        color: #FFFFFF !important;
    }

    .form {
        display: flex;
        flex-direction: row;
        justify-content: start;
        width: 20%;
    }

    .right-content {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        /* width: 20%; */
    }

    .review-training,
    .settings-cog {
        margin-right: 20px;
        cursor: pointer;
    }

    .review-training a {
        font-size: medium !important;
    }

    .settings-cog:hover {
        color: #0d6efd;
    }

    .mark {
        background-color: #1aa260;
        border: darkgreen 1px solid;
        padding: 3px;
        color: white;
    }

    .feedback-form-holder {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        margin-top: 10%;
        margin-bottom: 20%;
        margin-left: 25%;
        margin-right: 25%;
        text-align: center;
        padding: 15px;
    }

    .feedback-form {
        display: grid;
        justify-content: center;
        gap: 10px;

    }
</style>