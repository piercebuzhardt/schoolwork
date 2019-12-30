<?php 
header("Content-type: text/css");
$wrapperHeight = 100;
 ?>

/***********************************
Universal styles for the web site
***********************************/
html,body /* Apply styles to the html and body elements */
	{
		font-size:12pt;
		font-family:verdana;	
		color:#000000;
		background-color:#D2D2D2;
		width:100%;
		height:100%;
		margin:0px;	
	}
	
#wrapper
{
		position:relative;
		margin:auto;
		width:1100px;
		height:auto;
		min-height:100%;
		background-color:#ffffff;
	}
header
	{
		padding-bottom:1%;
		font-size:200%;
		font-weight:bold;
		text-align:center;
		height:10%;
	}
#header
	{
		padding-top: 2%;
	}
footer
	{
		position:absolute;
		bottom:0%;
		width:1000px;
		height:-100px;
		text-align:center;
		font-size:80%;
		font-weight:bold;
		margin-bottom:2%;
	}
	
.push{
	height:100px;
}
	
main
	{
		
		margin-top:3%;
		height:100%;
		width:100%;
		top:14%;	
		
	}
nav
	{
		width:100%;	
		height:3%;
		text-align:center;
	}
nav a:link,nav a:visited
	{
		color:rgb(0,0,0);
		text-decoration:none;
		margin: 7%;
	}
nav a:hover
	{
		color:#ff0000;
	}
#copyright
	{
		margin-top:2%;
	}
#footer_nav
	{
		margin-top:2%;
	}
#footer_nav a:link, #footer_nav a:visited
	{
		color:rgb(0,0,0);
		text-decoration:none;
		font-size:120%;
		margin:5%;
	}
	
#testing
{
	padding-top:1%;
	padding-right:1%;
	font-size:10pt;
	text-align:right;
}

.dropdown 
{
	z-index:1000;
    	position: relative;
    	display: inline-block;
	margin-left: 4%;
	margin-right: 4%;
}

.dropdown-content 
{
	text-align:left;
	display: none;
	position: absolute;
	min-width: 120px;
	box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
}

.dropdown-content a
{
	text-align:left;
	color: black;
	text-decoration: none;
	padding-top: 3%;
	padding-bottom: 3%;
	display: block;
}

.dropdown:hover .dropdown-content
{
	display: block;
	background-color: #f9f9f9;
}

.button {
    font-family: inherit;
    font-size: 100%;
    padding: .1em .3em;
    color: #444;
    color: rgba(0,0,0,.8);
    border: 1px solid #999;
    border: transparent;
    background-color: #E6E6E6;
    text-decoration: none;
    border-radius: 2px;
    cursor: pointer;
}

.tabledesign
{
	 border-collapse: collapse;
	 border: 1px solid black;
}

#rform
{
	display:inline-block;
}
