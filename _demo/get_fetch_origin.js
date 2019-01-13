function get_fetch_origin(method, url, result_id) {
    fetch(url, {method: method}).then(function (response) {
        if (method == "HEAD") {
            document.getElementById(result_id).textContent = response.headers.get("X-Reflected-Origin");
        } else {
            response.text().then(function (content) {
                document.getElementById(result_id).textContent = content;
            });
        }
    });
}
