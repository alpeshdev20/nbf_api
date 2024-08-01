<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription plan</title>
</head>

<body>

    <p>Dear {{ $user->name }},</p>
    <p>Your payment has been successful.</p>
    <p>Plan name - {{ $plan->name }}</p>
    <p>Amount paid - {{ $plan->price }}</p>
    <p>Expiry - {{ $subscriber->plan_end_date }}</p>
    <p>Feel free to contact us in case of query.</p>
    <p>Thanks,</p>
    <p>Team Netbookflix</p>
</body>

</html>
