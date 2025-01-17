<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hair Salon Appointment Reminder</title>
</head>

<body style="font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #dddddd;">
        <header style="width: 100dvw; display: flex; justify-content: center">
            <img src="https://pierakstspie.lv/static/media/logo1.4715ec4c15f0e733ca76.png" style="width: 60px;"/>
            <h2>pierakstspie.lv</h2>
        </header>
        <div style="padding: 20px; text-align: center;">
            <h1 style="color: #333333;">Atgādinājums par vizīti!</h1>
            <p style="color: #666666; font-size: 16px;">Pieraksta detaļas:</p>
            <table style="width: 100%; margin: 20px 0; border-collapse: collapse;">
                @inject('carbon', 'Carbon\Carbon')

                <tr>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">Datums:</td>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">{{$carbon::parse($booking['date'])->format( "d.m.Y" )}}</td>
                </tr>
                <tr>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">Laiks:</td>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">{{$carbon::parse($booking['date'])->format( "H:i" )}}</td>
                </tr>
           
                <tr>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">Pakalpojums:</td>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">{{$service['name']}}</td>
                </tr>
            
                <tr>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">Speciālists:</td>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">{{$specialist['name']}}</td>
                </tr>
                <tr>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">Atrašanās vieta:</td>
                    <td style="color: #333333; font-size: 16px; padding: 10px; border: 1px solid #dddddd;">{{$specialist['adress']}}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>