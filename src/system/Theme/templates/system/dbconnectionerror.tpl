<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Website temporarily unavailable</title>
        <style type="text/css">
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: Verdana, Arial, Helvetica, Sans-serif;
                font-size: 14px;
                background: #F2F2F2; /*non-CSS3 browsers*/
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FAFAFA', endColorstr='#eaeaea'); /*IE*/
                background: -webkit-gradient(linear, left top, left bottom, from(#FAFAFA), to(#eaeaea) ); /*webkit*/
                background: -moz-linear-gradient(center top , #FAFAFA, #eaeaea) repeat scroll 0 0 transparent; /*gecko*/
                background: linear-gradient(center top , #FAFAFA, #eaeaea) repeat scroll 0 0 transparent; /*CSS3*/
                line-height: 1.6em;
            }
            a {
                color: #2147B3;
                font-weight: bold;
                border: none;
            }
            img {
                border: none;
            }
            h1 {
                font-size:24px;
                line-height:28px;
            }
            h2 {
                color:#770000;
                font-size:22px;
                line-height:26px;
                text-transform:uppercase;
            }
            .container {
                display: table;
                height: 100%;
                width: 100%;
            }
            .cell {
                display: table-cell;
                vertical-align: middle;
                /* For IE6/7 */
                position: relative;
                top:expression(this.parentNode.clientHeight/2 - this.firstChild.clientHeight/2 + " px");
            }
            .content {
                /* center horizontally */
                margin: 0 auto;
                width: 50%;
                padding: 1.5em;
                background: #FAFAFA;
                border: 1px solid #2147B3;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                -moz-box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="cell">
                <div class="content">
                    <h1>Zikula Application Framework</h1>
                    <h2>Problem in database connection</h2>
                    <p>
                        This website is experiencing temporary technical difficulties, possibly due to high traffic.  If you can, please inform the website administrator of this problem, stating the time you saw this page.
                    </p>
                    <p>
                        This website is powered by the Zikula Application Framework, although run independantly by the website administrator. Please do not contact the Zikula team about this error, as it is specfic to this website.
                    </p>
                    <p>
                        <strong>If you are the website administrator...</strong><br/>
                        Zikula is unable to connect to your database. Please ensure your database access details are correct. Also, check to make sure your database is running correctly.<br />
                        <?php
                        if (System::getVar('development')) {
                            echo('Error: ' . $e->getMessage());
                        }
                        ?>
                    </p>
                    <p>
                        Zikula is free software released under the LGPL v3.  For more information, please visit <a href="http://zikula.org" title="Zikula Homepage">http://zikula.org</a>.
                    </p>
                    <p>
                        <a href="http://zikula.org"><img src="images/powered/small/cms_zikula.png" alt="Proudly powered by Zikula" width="80" height="15" /></a>
                        <a href="http://www.php.net"><img src="images/powered/small/php_powered.png" alt="PHP Language" width="80" height="15" /></a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>


