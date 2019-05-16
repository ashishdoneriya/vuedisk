# Readme

## Gallery images are not displaying?

Increase memory limit and execution time limit (maximum the better). Vuedisk generates thumbnails when you open that directory. Therefore it could be slow ( or may be very slow). When you click on any image thumbnail, then it generates another image thumbnail.

This is because lets say I have an Image whose size is 10 MB. So at first it generates image of height 320px (~ 150 KB). When you click on that image, it generates image of height 720px (~ 2MB). And when you click download button, it downloads original image (10 MB).

To counter this slowness, I created a golang program (which is very fast at converting images) which was to be called by the thumbnail.php but php restricted to call exec() method. So if you php server allows calling exec() method then you can try it (rename apis/thumbnail-next.php to apis/thumbnail.php)