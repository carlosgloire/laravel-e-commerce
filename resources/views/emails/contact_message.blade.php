<!DOCTYPE html>
<html>
<head>
    <title>Contact Message</title>
    <style>
        .logo { max-width: 200px; height: auto; }
        
    </style>
</head>
<body>
    
    <img src="cid:logo.png" alt="Company Logo"> 

    <h2>New Contact Message</h2>
    <p><strong>Name:</strong> {{ $contact->name }}</p>
    <p><strong>Email:</strong> {{ $contact->email }}</p>
    <p><strong>Phone:</strong> {{ $contact->phone }}</p>
    <p><strong>Comment:</strong> {{ $contact->comment }}</p>
</body>
</html>