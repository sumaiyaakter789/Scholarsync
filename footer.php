<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScholarSync</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .scholar-footer {
            background-color: #f9f9f9;
            padding: 40px 20px 20px;
            font-family: 'Segoe UI', sans-serif;
            color: #333;
            
        }

        .footer-container {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
            gap: 50px;
            margin-bottom: 20px;
        }

        .footer-logo img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 20px;
            margin-top: -5px;
            

        }
        .footer-logo{
            
            width: 50px;
            height: 50px;
        
            margin-bottom: -20px;
        
        }
        .footer-links {
            display: flex;
            gap: 5px;  
        }

        .link-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
            margin-left: 10px;
            margin-top: -60px;
        }



        .link-group a {
            text-decoration: none;
            color: #444;
            font-size: 14px;
        }

        .link-group a:hover {
            text-decoration: underline;
            color: #007acc;
        }

        .icon {
            width: 18px;
            height: 18px;
            vertical-align: middle;
            margin-right: 5px;
        }

        .footer-bottom {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin-top: 10px;
        }

        .footer-bottom a {
            color: #007acc;
            text-decoration: none;
        }

        .footer-bottom a:hover {
            text-decoration: underline;
        }
        .link-group-1{
            display: flex;
            flex-direction: row;
            gap: 7px;
            margin-left: 20px;
            margin-top: -50px;
            font-size: 13px;
            color: #666;
            justify-content: center;
        }
        .link-group-1 a {
            text-decoration: none;
            color: #444;
            font-size: 14px;
            
        }

    </style>
</head>
<body>

<footer class="scholar-footer">
    <div class="footer-logo">
        
    </div>
    <div class="footer-container">
       
        <div class="footer-links">
            <div class="link-group">
                <div class="footer-logo">
                    <img src="logo.jpg">
                </div>
            </div>
            <div class="link-group">
                <a href="##">About</a>
            </div>
            <div class="link-group">
                <a href="#">Press</a>
            </div>
            <div class="link-group">
                <a href="#">Papers</a>
            </div>
            <div class="link-group">
                <a href="##">Topics</a>
            </div>
            <div class="link-group">
                <a href="##">ScholarSync.edu Journals</a>
                
            </div>
            <div class="link-group">
                <a href="##"><img src="images/hiring_icon.png" class="icon"> Web Hiring!</a>
               
            </div>
            <div class="link-group">
                <a href="##"><img src="images/help_icon.jpg"  class="icon"> Help Center</a>
            </div>
           
        </div>
        
    </div>
    <div class="link-group-1">
        <a href="#">Find new research papers in : </a>
        <a href="#">Physics</a>
        <a href="#">Chemistry</a>
        <a href="#">Biology</a>
        <a href="#">Cognitive Science</a>
        <a href="#">Mathematics</a>
        <a href="#">Computer Science</a>
        <a href="#">Health Science</a>
        <a href="#">Ecology</a>
        <a href="#">Earth Science</a>
    </div>
    <div class="footer-bottom">
        <p>Terms &nbsp;|&nbsp; <a href="#">Privacy</a> &nbsp;|&nbsp; <a href="#">Copyright</a> &nbsp;|&nbsp; ScholarSync 2025</p>
    </div>
</footer>

    
</body>
</html>