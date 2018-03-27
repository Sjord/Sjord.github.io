<?php
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header('Content-Disposition: attachment; filename="vulnbingo.docx"');
chdir("/home/users/sjoevftp/vulnbingo");
passthru("/home/users/sjoevftp/vulnbingo/venv/bin/python /home/users/sjoevftp/vulnbingo/vulnbingo.py -");
