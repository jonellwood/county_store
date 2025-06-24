<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="./alarm.css" media=”screen” />
    <title>Enter Fire Alarm</title>
</head>

<body>
    <form name="aws-alarm" action="submit-fire-alarm.php" method="post">
        <h3>Enter Fire Alarm Information using this form.</h3>
        <fieldset id="address-data">
            <h4>Alarm Location Information</h4>
            <label for="IncStreetNum">Street Number</label>
            <input type="text" name="IncStreetNum">

            <label for="IncPreDir">Directional</label>
            <select name="IncPreDir">
                <option value="N">North</option>
                <option value="E">East</option>
                <option value="S">South</option>
                <option value="W">West</option>
            </select>
            <label for="IncStreetName">Street Name</label>
            <input type="text" name="IncStreetName">
            <label for="IncAptLoc">Apartment, Lot, or Suite Number</label>
            <input name="IncAptLoc" type="text" default="">
            <br>
        </fieldset>

        <fieldset id="company-data">
            <h4>Alarm Company Information</h4>
            <label for="AlarmCompanyName">Alarm Company Name</label>
            <input name="AlarmCompanyName" type="text">
            <br>
            <label for="CallerName">Caller Name</label>
            <input name="CallerName" type="text">
            <label for="CallerPhone">Caller Phone</label>
            <input name="CallerPhone" type="phone">
            <label for="CallerExtension">Caller Extension</label>
            <input name="CallerExtension" type="text" default="none">
        </fieldset>
        <fieldset id="alarm-data">
            <h4>Alarm Details</h4>
            <label for="FireAlarmType">Fire Alarm Type</label>
            <select name="FireAlarmType">
                <option value="Smoke" selected>Smoke</option>
                <option value="Heat">Heat</option>
                <option value="Carbon Monoxide">Carbon Monoxide</option>
                <option value="Panel Activation">Panel Activation</option>
                <option value="Other">Other</option>
            </select>
            <label for="AreaActivated">Area Activated</label>
            <input name="AreaActivated" type="text">
            <br>
            <label for="AnyInjuries">Anyone injured</label>
            <select name="AnyInjuries">
                <option value="No" selected>No</option>
                <option value="Yes">Yes</option>
            </select>
            <label for="RefNumber">Ref Number</label>
            <input name="RefNumber" type="text">
            <label for="PriorityAlias">Priority Alias</label>
            <input name="PriorityAlias" type="text">
            <label for="AlarmLevel">Alarm Level</label>
            <input name="AlarmLevel" type="text">
        </fieldset>
        <fieldset id="owner-contact-data">
            <label for="OwnerName">Property Owner Name</label>
            <input name="OwnerName" type="text">
            <label for="OwnerPhone">Property Owner Phone</label>
            <input name="OwnerPhone" type="phone" default="0000000000">
            <label for="CodesOrKeys">Code or Keys</label>
            <select name="CodesOrKeys">
                <option value="no" selected>No</option>
                <option value="yes">Yes</option>
            </select>
        </fieldset>


        <br>
        <button type="submit">Submit</button>
    </form>

</body>

</html>

<style>
    * {
        margin: 0;
        padding: 0;
    }

    body {
        background-image: radial-gradient(farthest-corner circle at 100% 0%,
                #67004e 0%,
                #8c00ea 100%);
        margin: 10px;
    }

    h3 {
        font-size: x-large;
    }

    fieldset {
        margin-top: 20px;
        border: 1px solid hotpink;
        padding: 20px;
        padding-bottom: 30px;
        color: #D3D3D3;
    }

    fieldset h4 {
        margin-top: -28px;
        color: white;
        font-size: x-large;
        font-weight: bold;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    fieldset input {
        margin-right: 5px;
        padding: 4px;
        margin-bottom: 15px;
    }
</style>