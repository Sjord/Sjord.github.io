<?php
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header('Content-Disposition: attachment; filename="vulnbingo.docx"');
chdir("/data/sites/web/sjoerdlangkempernl/subsites/vulnbingo");
passthru("/data/sites/web/sjoerdlangkempernl/subsites/vulnbingo/venv/bin/python /data/sites/web/sjoerdlangkempernl/subsites/vulnbingo/vulnbingo.py -");
