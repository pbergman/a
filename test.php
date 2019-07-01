<?php


//$descriptorspec = array(
//    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
//    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
//    2 => array("file", "/tmp/error-output.txt", "a") // stderr is a file to write to
//);

//$tmp = fopen('/tmp/error-output.txt', 'wb+');
//fwrite($tmp, "#!/bin/bash\nssh -tq testing 'cd ; bash --login'");
//fclose($tmp);

$descriptorspec = array(
    0 => array('pty'),
    1 => array('pty'),
    2 => array('pty'),
    61 => array("pipe", "r"),
);

//$cwd = '/tmp';
//$env = array('some_option' => 'aeiou');

$process = proc_open('exec </dev/fd/61', $descriptorspec, $pipes);

if (is_resource($process)) {

//    file_put_contents('php://fd/3', "#!/bin/bash\nssh -tq testing 'cd ; bash --login'");
//
//    $fd = fopen('php://fd/61', 'w+');
//    fwrite($fd, "#!/bin/bash\nssh -tq testing 'cd ; bash --login'");

    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt
/*    fwrite($pipes[0], '<?php print_r($_ENV); ?>');*/
//    fclose($pipes[0]);
//
    fwrite($pipes[61], "#!/bin/bash\nssh -tq testing 'cd ; bash --login'");
    fclose($pipes[61]);

//    fwrite($pipes[61], "read year");
//    fclose($pipes[61]);
//    echo stream_get_contents($pipes[1]);
//    fclose($pipes[0]);
//    fclose($pipes[1]);
//    fclose($pipes[2]);



    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    echo "command returned $return_value\n";
}