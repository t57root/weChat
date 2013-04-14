#WeChat php class.

###Functions: 
* send message to specific user by fakeid
* get fakeid to openid mapping
* Login session keeping

There's some bugs in HttpClient.class.php.
i've made a patch as shown below:
<pre>
--- old/HttpClient.class.php    2270-01-13 02:20:48.000000000 +0800
+++ new/HttpClient.class.php  2013-04-14 03:23:33.057576718 +0800
@@ -42,8 +42,10 @@
     function HttpClient($host, $port=80) {
         $this->host = $host;
         $this->port = $port;
+        $this->cookie_host = $host;
     }
     function get($path, $data = false) {
+        $this->postdata='';
         $this->path = $path;
         $this->method = 'GET';
         if ($data) {
@@ -336,4 +338,4 @@
     }   
 }
</pre>


