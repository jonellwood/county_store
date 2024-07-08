<script>
    function hideBanner() {
        document.getElementById("alert-banner").style.display = "none";
    }
</script>

<div class="alert-banner" id="alert-banner">
    <div class="alert-text">🚨 All orders must be submitted by May 31st, Requests will not be able to be submitted between June 1st and June 30th. </div>
    <div class="holder">
        <p>
            <button class="button" onclick="hideBanner()">OK</button>
        </p>
        <p>
            <label for="checkbox">Don't show again</label>
            <input type="checkbox" id="checkbox">
        </p>
    </div>
</div>

<style>
    .alert-banner {
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: red;
        color: white;
        width: 100%;
        padding: 10px;
        position: relative;
        top: 100px;
        text-align: center;
        font-size: larger;
        padding-left: 20px;
        padding-bottom: 20px;
        gap: 25px;
    }
</style>