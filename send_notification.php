<?php
function send_notification($to, $subject, $message) {
    $headers = "From: lostfound@college.edu\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    @mail($to, $subject, $message, $headers);
}
?>