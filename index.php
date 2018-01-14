<?php
###############################################################
# Zipit Backup Utility
###############################################################
# Developed by Jereme Hancock for Cloud Sites
# Visit http://zipitbackup.com for updates
###############################################################

// include password protection
require_once("zipit-login.php"); 

// require zipit configuration
require('zipit-config.php');

// define zipit log file
    $zipitlog = "../../../logs/zipit.log";
    $logsize = filesize($zipitlog);

// create zipit log file if it doesn't exist
if (!file_exists("$zipitlog")) { 
   $fp = fopen("$zipitlog","w");  
   fwrite($fp,"----Zipit Logs----\n\n");  
   fclose($fp); 
}

// rotate log file to keep it from growing too large
if ($logsize > 52428800) {
   shell_exec("mv ../../../logs/zipit.log ../../../logs/zipit_old.log");
}

// clean up local file backups if files are older than 24 hours (86400 seconds)
$dir = "./zipit-backups/files/";
 
if ($handle = opendir($dir)) {
   while (($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || is_dir($dir.'/'.$file)) {
         continue;
      }
      if ($file != "index.php") {
         if ((time() - filemtime($dir.'/'.$file)) > 86400) {
            shell_exec("rm $dir/$file");
         }
      }
   }
   closedir($handle);
}

// clean up local database backups if files are older than 24 hours (86400 seconds)
$dir = "./zipit-backups/databases/";
 
if ($handle = opendir($dir)) {
   while (($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || is_dir($dir.'/'.$file)) {
         continue;
      }
      if ($file != "index.php") {
         if ((time() - filemtime($dir.'/'.$file)) > 86400) {
            shell_exec("rm $dir/$file");
         }
      }
   }
   closedir($handle);
}

// clean up backup progress files older than 24 hours (86400 seconds)
$dir = ".";
 
   if ($handle = opendir($dir)) {
      while (($file = readdir($handle)) !== false) {
         if ($file == '.' || $file == '..' || is_dir($dir.'/'.$file)) {
            continue;
         }
         if (substr($file,-13) == "-progress.php") {
            if ((time() - filemtime($dir.'/'.$file)) > 86400) {
               shell_exec("rm $dir/$file");
            }
         }
      }
      closedir($handle);
   }

// clean up updater progress files older than 24 hours (86400 seconds)
$dir = "..";
 
if ($handle = opendir($dir)) {
   while (($file = readdir($handle)) !== false) {
      if ($file == '.' || $file == '..' || is_dir($dir.'/'.$file)) {
         continue;
      }
      if (substr($file,-13) == "-progress.php") {
         if ((time() - filemtime($dir.'/'.$file)) > 86400) {
            shell_exec("rm $dir/$file");
         }
      }
   }
   closedir($handle);
}

// generate hash to create progress file
$progress_hash_files_continuous = substr(hash("sha512",rand()),0,12); 
$progress_hash_files_weekly = substr(hash("sha512",rand()),0,12); 
$progress_hash_databases_continuous = substr(hash("sha512",rand()),0,12); 
$progress_hash_databases_weekly = substr(hash("sha512",rand()),0,12); 

// get installed version
$installed_version = "zipit-version.php";
$fh = fopen($installed_version, 'r');
$display_version = fread($fh, 5);
fclose($fh);

// check for new version
shell_exec('wget https://raw.github.com/jeremehancock/zipit-backup-utility/master/zipit-version.php --no-check-certificate -O zipit-latest.php');
$latest_version = "zipit-latest.php";
$fh = fopen($latest_version, 'r');
$latest_version = fread($fh, 5);
fclose($fh)

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="google" value="notranslate">
<title>Zipit Backup Utility</title>
<link rel="stylesheet" href="css/colorbox.css" />
<link rel="stylesheet" href="css/zipit/jquery-ui.css" />
<link href="css/style.css" rel="stylesheet" type="text/css">

<style>
body {
   font: 1em "Arial", sans-serif;
   background: url(images/background.jpg) no-repeat center center fixed; 
   -webkit-background-size: cover;
   -moz-background-size: cover;
   -o-background-size: cover;
   background-size: cover;
   background-color:#7397a7;
}
</style>

<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,400italic' rel='stylesheet' type='text/css'>
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">

<script src="js/jquery.js"></script>
<script src="js/jquery-ui.js"></script>

<script src="js/jquery.colorbox.js"></script>

<script>
   $(document).ready(function(){$(".backup-files").colorbox({iframe:true, innerWidth:"400px", innerHeight:"70px", closeButton:false, escKey:false, overlayClose:false, scrolling:false, top: "220px" });});
   $(document).ready(function(){$(".backup-database").colorbox({iframe:true, innerWidth:"400px", innerHeight:"70px", closeButton:false, escKey:false, overlayClose:false, scrolling:false, top: "220px" });});
   $(document).ready(function(){$(".add-db").colorbox({iframe:true, innerWidth:"400px", innerHeight:"350px", closeButton:true, escKey:true, overlayClose:true, scrolling:false, top: "85px" });});
   $(document).ready(function(){$(".add-profile").colorbox({iframe:true, innerWidth:"400px", innerHeight:"600px", closeButton:true, escKey:true, overlayClose:true, scrolling:false, top: "55px" });});
   $(document).ready(function(){$(".edit-db").colorbox({iframe:true, innerWidth:"400px", innerHeight:"350px", closeButton:true, escKey:true, overlayClose:true, scrolling:false, top: "85px" });});
   $(document).ready(function(){$(".update").colorbox({iframe:true, innerWidth:"400px", innerHeight:"70px", closeButton:false, escKey:false, overlayClose:false, scrolling:false, top: "220px" });});
   $(document).ready(function(){$(".settings").colorbox({onOpen: function() {$("#settings-tip").tooltip("disable");},onClosed: function() {$("#tt").tooltip("enable");},iframe:true, innerWidth:"650px",innerHeight:"550px", closeButton:true, escKey:true, overlayClose:true, scrolling:false, top: "35px" });});
</script>

<script>
   $(function() {
      $( document ).tooltip({
         position: {
            my: "center bottom-20",
            at: "center top",
            using: function( position, feedback ) {
               $( this ).css( position );
               $( "<div>" )
               .addClass( "arrow" )
               .addClass( feedback.vertical )
               .addClass( feedback.horizontal )
               .appendTo( this );
            }
         }
      });
   });
</script>

<script>
// Disable caching of AJAX responses
   $.ajaxSetup ({
      cache: false
   });
</script>

</head>
<body>
<a href="https://github.com/jeremehancock/zipit-backup-utility" target="_blank"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_gray_6d6d6d.png" alt="Fork me on GitHub"></a>
<div id="wrapper">
   <h1>Zipit Backup Utility <div class="version_info" id="version_info">v<?php echo $display_version; ?></h1>
   <div id="logout">
      <a class='settings' href="zipit-settings.php"><img src="images/settings.png" class="settings-tip" title="Settings"/></a>
      <a href="index.php?logout=1" title="Logout"><img src="images/logout.png" /></a>
   </div>
   <div id="tabContainer">
      <div id="tabs">
         <ul>
            <li class="clickHome" id="tabHeader_1">Home</li>
            <li class="clickFiles" id="tabHeader_2">Files</li>
            <li class="clickDatabases" id="tabHeader_3">Databases</li>
            <li class="clickScheduler" id="tabHeader_4">Scheduler</li>
            <li class="clickLogs" id="tabHeader_5">Logs</li>
            <li class="clickTroubleshooting" id="tabHeader_6">Troubleshooting Tips</li>
         </ul>
      </div>
      <div id="tabscontent">
         <div class="tabpage" id="tabpage_1">
            <p><br/>The Zipit Backup Utility was designed for use with <a href="http://www.rackspace.com/cloud/sites/" target="_blank">Rackspace Cloud Sites&reg; <img src='images/open_in_new_window.png' /></a>.<br/><br/>Please note Zipit is not part of the Rackspace&reg; portfolio, and thus, is an “Unsupported Service”.<br/><br/><h5>Additional info:</h5><a href="http://www.rackspace.com/knowledge_center/article/zipit-backup-utility" target="_blank">Knowledge Center Article <img src="images/open_in_new_window.png" /></a><br/><a href="https://community.rackspace.com/products/f/26/t/445" target="_blank">Community Forums <img src="images/open_in_new_window.png" /></a><br/><a href="https://github.com/jeremehancock/zipit-backup-utility" target="_blank">Github Page <img src="images/open_in_new_window.png" /></a></p>
            <p><?php if ($display_version < $latest_version) {echo "<br/>There is a new version of Zipit available! <br/><br/><a id='update' class='update' href='zipit-updater.php'><button type='button' class='css3button'>Update Now</button></a><br/><br/><em><font color='red'>Your current version will backed up in a time-stamped folder to preserve any modifications that you may have made. It is safe to remove the backed up version once the update is complete.</font></em>";} ?></p>
         </div>
         
         <div class="tabpage" id="tabpage_2" style="display: none;">
            <h2>Available File Backups <img src="images/hint.png" title="This is the list of file backups that you have available in your Cloud Files account created by Zipit. Use the link below the list to manage these backups." /></h2>
            <center><iframe src="zipit-view-files.php" class="files_frame" frameborder="0" scrolling="auto" name="files-list"></iframe><br/><br/></center>
            <?php echo "<center>Manage your file backups via the <a href='https://mycloud.rackspace.com/a/$username/files#object-store%2CcloudFiles%2CORD/zipit-backups-files-$url/' target='_blank'>Cloud Files control panel <img src='images/open_in_new_window.png' /></a>";	echo "<br/>"; echo "</center></em><br/>"; ?>
            
            <div id="profile_menu" class="profile_menu"><!-- profile menu loads here --></div>
            <p><center><a id="backup-files" class="backup-files" style="display:none;padding-right:15px;" href=""><button type="button" class="css3button">Backup Now</button></a><span id="delete-profile" class='delete-profile' style="display:none;padding-right:15px;" ><button type="button" class="css3button" onclick="return confirmDeleteProfile();">Delete Profile</button></span><a class='add-profile' style="padding-right:15px;" href="zipit-add-profile.php"><button type="button" class="css3button" onclick="updateProfileMenu();">Add Profile</button></a><button type="button" class="css3button" id="refresh-files" onclick="refreshFiles();">Refresh List</button></center></p>

<script>
   function showFilesBackupButton() {
      var val = document.profile_form.profile_select.value; 
      if (val == "Select Profile for Backup") {
         document.getElementById("backup-files").style.display="none"; 
         document.getElementById("delete-profile").style.display="none";
      }
      
      else if (val == "Full-Backup-Default") {
         document.getElementById("backup-files").style.display="";  
         document.getElementById("delete-profile").style.display="none";
      }
      
      else {
         document.getElementById("backup-files").style.display="";  
         document.getElementById("delete-profile").style.display="";
      }
   }

   function display_Profile_Schedule() {
      var val = document.profile_form_schedule.profile_select_schedule.value; 
      if (val == "Select Profile for Backup") {
         document.getElementById("files_continuous").value="No Profile Selected"; 
         document.getElementById("files_weekly").value="No Profile Selected"; 
      }
      else {
         document.getElementById("files_continuous").value="web/content/zipit/zipit-zip-files-worker.php <?php echo $auth_hash.' '.$progress_hash_files_continuous;?>"+val+" auto norotate " +val; 
         document.getElementById("files_weekly").value="web/content/zipit/zipit-zip-files-worker.php <?php echo $auth_hash.' '.$progress_hash_files_weekly;?>"+val+" auto weekly " +val; 
      }
   }

   function updateProfile(objS) {
      document.getElementById("backup-files").href = "zipit-zip-files.php?profile=" + objS.options[objS.selectedIndex].value;
   }

   $(document).ready(function(){
      $("#profile_menu").load("zipit-profile-menu.php");
      $("#profile_menu_schedule").load("zipit-profile-menu-schedule.php");
   });

   function updateProfileMenu() {
      $("#profile_menu").load("zipit-profile-menu.php");
      document.getElementById("backup-files").style.display="none"; 
      document.getElementById("delete-profile").style.display="none";
   }

   function updateProfileMenuSchedule() {
      $("#profile_menu_schedule").load("zipit-profile-menu-schedule.php");
      document.getElementById("files_continuous").value="No Profile Selected"; 
      document.getElementById("files_weekly").value="No Profile Selected"; 
   }
   
</script>
<script>
   function confirmDeleteProfile() {
      var val = document.profile_form.profile_select.value;
      if (confirm('Are you sure you want to delete the \"' +val+ '\" profile?\n\nThis can\'t be undone!')) {
         $.ajax({
            url: "zipit-delete-profile.php?profile=" +val,
            type: "POST",
            data: {id : 5},
            dataType: "html", 
            success: function() {
               updateProfileMenu();
            }
         });
      }
   }
   function refreshFiles() {
      var ifr = document.getElementsByName('files-list')[0];
      ifr.src = ifr.src;
   }
</script>

         </div>
         
         <div class="tabpage" id="tabpage_3" style="display: none;">
            <h2>Available Database Backups <img src="images/hint.png" title="This is the list of database backups that you have available in your Cloud Files account created by Zipit. Use the link below the list to manage these backups." /></h2>
            <center><iframe src="zipit-view-db.php" class="dbs_frame" frameborder="0" scrolling="auto" name="db-list"></iframe><br/><br/></center>
            <?php echo "<center>Manage your database backups via the <a href='https://mycloud.rackspace.com/a/$username/files#object-store%2CcloudFiles%2CORD/zipit-backups-databases-$url/' target='_blank'>Cloud Files control panel <img src='images/open_in_new_window.png' /></a>"; echo "<br/>"; echo "</center></em><br/>"; ?>

<script>
   function showDBBackupButton() {
      var val = document.db_form.db_select.value; 
      if (val == "Select Database to Backup") {
         document.getElementById("backup-database").style.display="none"; 
         document.getElementById("edit-db").style.display="none";
      }
      else {
         document.getElementById("backup-database").style.display="";  
         document.getElementById("edit-db").style.display="";
      }
   }

   function display_Schedule() {
      var val = document.db_form_schedule.db_select_schedule.value; 
      if (val == "Select Database to Backup") {
         document.getElementById("databases_continuous").value="No Database Selected"; 
         document.getElementById("databases_weekly").value="No Database Selected"; 
      }
      else {
         document.getElementById("databases_continuous").value="web/content/zipit/zipit-zip-db-worker.php <?php echo $auth_hash.' '.$progress_hash_databases_continuous;?>"+val+" "+val+ " auto "; 
         document.getElementById("databases_weekly").value="web/content/zipit/zipit-zip-db-worker.php <?php echo $auth_hash.' '.$progress_hash_databases_weekly;?>"+val+" "+val+" auto weekly"; 
      }
   }

   function update(objS) {
      document.getElementById("backup-database").href = "zipit-zip-db.php?db=" + objS.options[objS.selectedIndex].value;
      document.getElementById("edit-db").href = "zipit-add-db.php?db=" + objS.options[objS.selectedIndex].value;
   }

   $(document).ready(function(){
      $("#db_menu").load("zipit-db-menu.php");
      $("#db_menu_schedule").load("zipit-db-menu-schedule.php");
   });

   function updateDbMenu() {
      $("#db_menu").load("zipit-db-menu.php");
      document.getElementById("backup-database").style.display="none"; 
      document.getElementById("edit-db").style.display="none";
   }

   function updateDbMenuSchedule() {
      $("#db_menu_schedule").load("zipit-db-menu-schedule.php");
      document.getElementById("databases_continuous").value="No Database Selected"; 
      document.getElementById("databases_weekly").value="No Database Selected"; 
   }

   function SelectAll(id) {
      document.getElementById(id).focus();
      document.getElementById(id).select();
   }
</script>

         <div id="db_menu" class="db_menu"><!-- database menu loads here --></div>
         <p><center><a id="backup-database" class="backup-database" style="display:none;padding-right:15px;" href=""><button type="button" class="css3button">Backup Now</button></a><a id="edit-db" class='edit-db' style="display:none;padding-right:15px;" href=""><button type="button" class="css3button">Edit Credentials</button></a><a class='add-db' style="padding-right:15px;" href="zipit-add-db.php"><button type="button" class="css3button" onclick="updateDbMenu();">Add Credentials</button></a><button type="button" class="css3button" id="refresh-db" onclick="refreshDb();">Refresh List</button></center></p>

<script>
   function refreshDb() {
      var ifr = document.getElementsByName('db-list')[0];
      ifr.src = ifr.src;
   }
</script>

         </div>
         
         <div class="tabpage" id="tabpage_4" style="display: none;">
            <h2>Scheduler</h2>
            You can easily automate Zipit via a Scheduled Task (cronjob) via the Cloud Sites Control Panel.<br/><br/>Below you will find the "Commands" to use for the Scheduled Task (cronjob).<br/><br/> Be sure to set the "Command Language" to php!  <br/><br/>For more info on setting up a Scheduled Task (cronjob) in Cloud Sites click <a href="http://www.rackspace.com/knowledge_center/article/how-do-i-schedule-a-cron-job-for-cloud-sites" target="_blank">here <img src='images/open_in_new_window.png' /></a>.<br/><br/>
            <div id="div1" class="alldivs"> <div id="profile_menu_schedule" class="profile_menu_schedule"><!-- profile menu loads here --></div><h4>Files Backup Options:</h4><br/>Backup: <img src="images/hint.png" style="width:13px" title="Use this command to create a new backup each time the Scheduled Task (cronjob) runs without any rotation. The backups will continue until the Scheduled Task (cronjob) is deleted." /><br/><input class="files_continuous" name="files_continuous" type="text" id="files_continuous" value="web/content/zipit/zipit-zip-files-worker.php <?php echo $auth_hash.' '.$progress_hash_files_continuous;?> auto" readonly onClick="SelectAll('files_continuous');"><br/><br/>
Weekly Rotation: <img src="images/hint.png" style="width:13px" title="Use this command to create a backup for each day of the week and rotate weekly. This will give you a maximum of 7 days of backups for your files. For this to function properly you must setup the Scheduled Task (cronjob) to run once per day. Keep in mind that when the rotation occurs the previous backup for that day will be overwritten and cannot be recovered!" /><br/><input class="files_weekly" name="files_weekly" type="text" id="files_weekly" value="web/content/zipit/zipit-zip-files-worker.php <?php echo $auth_hash.' '.$progress_hash_files_weekly;?> auto weekly" readonly onClick="SelectAll('files_weekly');">
            </div>

            <div id="div2" class="alldivs"> <div id="db_menu_schedule" class="db_menu_schedule"><!-- database menu loads here --></div><h4>Database Backup Options:</h4><br/>Backup: <img src="images/hint.png" style="width:13px" title="Use this command to create a new backup each time the Scheduled Task (cronjob) runs without any rotation. The backups will continue until the Scheduled Task (cronjob) is deleted." /><br/><input class="databases_continuous" name="databases_continuous" type="text" id="databases_continuous" value="No Database Selected" readonly onClick="SelectAll('databases_continuous');"><br/><br/>
Weekly Rotation: <img src="images/hint.png" style="width:13px" title="Use this command to create a backup for each day of the week and rotate weekly. This will give you a maximum of 7 days of backups for your database. For this to function properly you must setup the Scheduled Task (cronjob) to run once per day. Keep in mind that when the rotation occurs the previous backup for that day will be overwritten and cannot be recovered!" /><br/><input class="databases_weekly" name="databases_weekly" type="text" id="databases_weekly" value="No Database Selected" readonly onClick="SelectAll('databases_weekly');">
            </div>
         </div>
         
         <div class="tabpage" id="tabpage_5" style="display: none;">
            <h2>Logs</h2>
            <center><iframe src="zipit-logs.php" class="logs_frame" frameborder="0" scrolling="auto" name="logs-list"></iframe><br/><br/></center>
            <p><center><button type="button" class="css3button" id="refresh-logs" onclick="refreshLogs();">Refresh Logs</button>&nbsp;&nbsp;&nbsp;&nbsp;<button type="button" class="css3button" id="clear" onclick="return confirmClearLogs();">Clear Logs</button></center></p>

<script>
   function confirmClearLogs() {
      if (confirm('Are you sure you want to clear your log?\n\nThis can\'t be undone!')) {
         $.ajax({
            url: "zipit-clear-logs.php",
            type: "POST",
            data: {id : 5},
            dataType: "html", 
            success: function() {
               refreshLogs();
            }
         });
      }
   }

   function refreshLogs() {
      var ifr = document.getElementsByName('logs-list')[0];
      ifr.src = ifr.src;
   }
</script>

         </div>

         <div class="tabpage" id="tabpage_6" style="display: none;">
            <h2>Troubleshooting Tips</h2>

<script>
   $(function() {
      $( "#accordion" ).accordion({
         collapsible: true,
         heightStyle: "content",
         active: false
      });
   });
</script>

            <div id="accordion">
               <h3>Zip Failed!</h3>
               <div>
                  <div class="cause_fix">Cause:</div>
                     <p>This error generally indicates that your site/database is larger than 4gb once zipped.</p><br/>
                  <div class="cause_fix">Solution:</div>
                  <p>You will need to reduce the size of your site/database to fix this issue.</p>
               </div>
               
               <h3>Backup Failed Integrity Check!</h3>
               <div>
                  <div class="cause_fix">Cause:</div>
                     <p>This error indicates that the backup was moved to Cloud Files®,however, the integrity check on the backup failed. This generally indicates that the backup was corrupted during transfer. This can be caused by various network factors.</p><br/>
                  <div class="cause_fix">Solution:</div>
                     <p>Check your internet connection and try again.</p>
               </div>
               
               <h3>Authorization Failed!</h3>
               <div>
                  <div class="cause_fix">Cause:</div>
                     <p>This error indicates that the "Auth Hash" that was used to run Zipit did not match the value within the Zipit configuration. This error is commonly seen if the Scheduled Task option is setup with the wrong "Auth Hash". </p><br/>
                  <div class="cause_fix">Solution:</div>
                     <p>Be sure that you use the exact command for the Scheduled Task that is found on the Scheduler tab. You can set the "Auth Hash" by clicking the Settings icon.</p>
               </div>
                  
               <h3>Can't Write to Log!</h3>
               <div>
                  <div class="cause_fix">Cause:</div>
                     <p>This error indicates that Zipit was unable to write to the zipit.log file in your logs folder. This is generally caused by having Zipit installed outside of the site's content folder. Another common cause is if Zipit was copied from one site to another using a secondary FTP user. </p><br/>
                  <div class="cause_fix">Solution:</div>
                     <p>Zipit is designed to be installed with the Zipit Installer and not moved manually. Be sure to use the Zipit Installer for the intial installation of Zipit and the Zipit Updater to update Zipit to any future version. You can see if there is a new version of Zipit available on the Home tab.</p>
                  </div>
               </div>
         </div>
    </div>
      <div class="dev_by" id="dev_by">Developed by: <a href="https://github.com/jeremehancock" target="_blank">Jereme Hancock</a></div>
   </div>
</div>

<script src="js/tabs.js"></script>

<script>
   $(function () {
      $('.clickHome').click(function () {
         refreshLogs();
         refreshDb();
         refreshFiles();
         updateDbMenu();
         updateDbMenuSchedule();
         updateProfileMenu();
         updateProfileMenuSchedule();
      });
   });

   $(function () {
      $('.clickFiles').click(function () {
         refreshFiles();
         updateProfileMenu();
      });
   });

   $(function () {
      $('.clickDatabases').click(function () {
         refreshDb();
         updateDbMenu();
      });
   });

   $(function () {
      $('.clickScheduler').click(function () {
         updateDbMenuSchedule();
         updateProfileMenuSchedule();
      });
   });

   $(function () {
      $('.clickLogs').click(function () {
         refreshLogs();
      });
   });

   $(function () {
      $('.clickTroubleshooting').click(function () {
         refreshLogs();
         refreshDb();
         refreshFiles();
         updateDbMenu();
         updateDbMenuSchedule();
         updateProfileMenu();
         updateProfileMenuSchedule();
      });
   });
</script>

<?php
if ($usage_feedback == "allow") {
   include("zipit-usage-feedback.php");
}
?>
</body>
</html>
