#!/usr/bin/env python3

import unittest
import requests

class TestDemo(unittest.TestCase):
    host = "https://demo.sjoerdlangkemper.nl/"

    def get_url(self, page):
        return self.host + page

    def test_bitflip(self):
        url = self.get_url("bitflip.php")
        assert "hobbies" in requests.get(url).text
        assert "XmreZ8v" in requests.get(url, params={"data": "971d0128a0544ba8efb5d87f58935377e5d28e8defe41e481c23ab90f491b40b8800744bb2bbae7d4d70e73f8ac9163bf0820dd5b100c07cdb579617d17dcbf0d21ef2f397effc6661a7dcfcb2fe5885"}).text
        assert "Welcome, administrator!" in requests.get(url, params={"data": "971d0128a0544ba8efb5d87f5893537727b61792b9ee207121b23bb9c297fabc47bac908ed7088cd05dafde0e0a22c6fa4fb64d40033207c81a222c4483f7c7fc192a08dfaa5a661548a004ed1f7d544fdef5e33e49c9b9fc60d43aa176d1bbc"}).text

    def test_compression(self):
        url = self.get_url("compression.php")
        assert "XmreZ8v" in requests.get(url, params={"search": "XmreZ8v"}).text
        contain_len = len(requests.get(url, params={"search": "determine"}, stream=True).raw.read())
        missing_len = len(requests.get(url, params={"search": "XmreZ8v"}, stream=True).raw.read())
        assert contain_len < missing_len

    def test_cors(self):
        url = self.get_url("cors.php")
        response = requests.get(url)
        assert "alert" in response.text
        assert response.headers["Access-Control-Allow-Origin"] == "*"

    def test_login(self):
        url = self.get_url("login.php")
        response = requests.post(url, data={"username": "a", "password": "a"})
        self.assertIn("Invalid CSRF token", response.text)

    def test_vulnbingo(self):
        url = self.get_url("vulnbingo.php")
        doc1 = requests.get(url).content
        doc2 = requests.get(url).content
        self.assertTrue(len(doc1) >= 10000)
        self.assertNotEqual(doc1, doc2)

    def test_auth_basic(self):
        url = self.get_url("auth/basic.php")
        self.assertIn("Authorization header missing", requests.get(url).text)
        self.assertIn("Authorization header received", requests.get(url, auth=requests.auth.HTTPBasicAuth("a", "a")).text)
        self.assertEqual(requests.get(url, params={"host": True}).headers["Access-Control-Allow-Origin"], "https://demo.sjoerdlangkemper.nl")

    def test_time(self):
        url = self.get_url("time.php")
        time = requests.get(url).text
        float(time)

if __name__ == "__main__":
    unittest.main()
