<?php

$uri = $_GET["uri"] ?? '';

function headerMimeTypes($extension) {
	$jsonMimeTypes = '[
            {"extension": "jpg",  "mimetype": "image/jpeg"},
            {"extension": "jpeg", "mimetype": "image/jpeg"},
            {"extension": "png",  "mimetype": "image/png"},
            {"extension": "gif",  "mimetype": "image/gif"},
            {"extension": "svg",  "mimetype": "image/svg+xml"},
            {"extension": "wav",  "mimetype": "audio/wav"},
            {"extension": "mp3",  "mimetype": "audio/mpeg"},
            {"extension": "pdf",  "mimetype": "application/pdf"},
            {"extension": "css",  "mimetype": "text/css"},
            {"extension": "js",   "mimetype": "text/javascript"},
            {"extension": "json", "mimetype": "application/json"},
            {"extension": "html", "mimetype": "text/html"},
            {"extension": "txt",  "mimetype": "text/plain"},
            {"extension": "xml",  "mimetype": "application/xml"},
            {"extension": "doc",  "mimetype": "application/msword"},
            {"extension": "docx", "mimetype": "application/vnd.openxmlformats-officedocument.wordprocessingml.document"},
            {"extension": "xls",  "mimetype": "application/vnd.ms-excel"},
            {"extension": "xlsx", "mimetype": "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"},
            {"extension": "ppt",  "mimetype": "application/vnd.ms-powerpoint"},
            {"extension": "pptx", "mimetype": "application/vnd.openxmlformats-officedocument.presentationml.presentation"},
            {"extension": "mp4",  "mimetype": "video/mp4"},
            {"extension": "avi",  "mimetype": "video/x-msvideo"},
            {"extension": "mov",  "mimetype": "video/quicktime"},
            {"extension": "flv",  "mimetype": "video/x-flv"},
            {"extension": "webm", "mimetype": "video/webm"},
            {"extension": "mkv",  "mimetype": "video/x-matroska"},
            {"extension": "zip",  "mimetype": "application/zip"},
            {"extension": "rar",  "mimetype": "application/x-rar-compressed"},
            {"extension": "tar",  "mimetype": "application/x-tar"},
            {"extension": "gz",   "mimetype": "application/gzip"},
            {"extension": "bz2",  "mimetype": "application/x-bzip2"},
            {"extension": "7z",   "mimetype": "application/x-7z-compressed"},
            {"extension": "ico",  "mimetype": "image/x-icon"},
            {"extension": "tiff", "mimetype": "image/tiff"},
            {"extension": "bmp",  "mimetype": "image/bmp"},
            {"extension": "psd",  "mimetype": "image/vnd.adobe.photoshop"},
            {"extension": "eps",  "mimetype": "application/postscript"},
            {"extension": "ai",   "mimetype": "application/postscript"},
            {"extension": "otf",  "mimetype": "font/otf"},
            {"extension": "ttf",  "mimetype": "font/ttf"},
            {"extension": "woff", "mimetype": "font/woff"},
            {"extension": "woff2","mimetype": "font/woff2"}
        ]';
	$mimeTypes = json_decode($jsonMimeTypes, true);
	$extension = strtolower($extension);
	header('Content-Type: '.$mimeTypes[$extension].'');
}

ob_clean(); 
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (str_starts_with($uri, "public/")) {
	$fileExtension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
	
	if (!file_exists($uri) || is_dir($uri)) {
		http_response_code(404);
		die("404 - Arquivo não encontrado.");
	}
	
	ob_clean(); // limpa qualquer saída anterior
	header('Content-Type: image/jpeg');
// 	headerMimeTypes($fileExtension);
	readfile($uri);
} else {
	http_response_code(404);
	echo "404 - File Not Found";
}