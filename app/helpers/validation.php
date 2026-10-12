<?php
function sanitizeInput($data) {
    return trim(stripslashes($data));
}