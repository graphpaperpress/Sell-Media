<?php

function start_log_txt( $file_handle ){
    $date = date( "F j, Y, g:i a" );
    $log_data = "-- Start Of Log: {$date} ------------------------------- \n";
    fwrite( $file_handle, $log_data );
}

function write_log_txt( $file_handle, $log_data ){
    fwrite( $file_handle, $log_data );
}

function end_log_txt( $file_handle ){
    $log_data = "-- End Of Log ---------------------------------------------------------\n\n\n";
    fwrite( $file_handle, $log_data );
    fclose( $file_handle );
}
