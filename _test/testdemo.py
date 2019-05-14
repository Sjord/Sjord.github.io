#!/usr/bin/env python3

import unittest
import requests

class TestDemo(unittest.TestCase):
    def test_bitflip(self):
        url = "http://demo.sjoerdlangkemper.nl/bitflip.php"
        assert "hobbies" in requests.get(url).text
        assert "XmreZ8v" in requests.get(url, params={"data": "fe8ea4aa0b4a96fe694146772f5238cc86ae0af584ad55555e948564ec195f2a90a0914cd722007eea154c04b7ab5a6983e009280cd635cd5f2ad606d1125bc5"}).text
        assert "Welcome, administrator!" in requests.get(url, params={"data": "138d0687975a4883d746a313e73bbd973ab2a4ef285762283c64d540ed3465ef01d4b288408508a9aa0748b602d4c25f1fbb22d523a02ff7c3e7d73e04f3f0f83e73ebce6634b68d82139749fb58887a04e54c045d6bd4c171c10a13ea406542"}).text

    def test_compression(self):
        url = "http://demo.sjoerdlangkemper.nl/compression.php"
        assert "XmreZ8v" in requests.get(url, params={"search": "XmreZ8v"}).text
        contain_len = len(requests.get(url, params={"search": "determine"}, stream=True).raw.read())
        missing_len = len(requests.get(url, params={"search": "XmreZ8v"}, stream=True).raw.read())
        assert contain_len < missing_len

    def test_cors(self):
        url = "http://demo.sjoerdlangkemper.nl/cors.php"
        response = requests.get(url)
        assert "alert" in response.text
        assert response.headers["Access-Control-Allow-Origin"] == "*"

    def test_login(self):
        url = "http://demo.sjoerdlangkemper.nl/login.php"
        response = requests.post(url, data={"username": "a", "password": "a"})
        self.assertIn("Invalid CSRF token", response.text)

    def test_vulnbingo(self):
        url = "http://demo.sjoerdlangkemper.nl/vulnbingo.php"
        doc1 = requests.get(url).content
        doc2 = requests.get(url).content
        self.assertTrue(len(doc1) >= 10000)
        self.assertNotEqual(doc1, doc2)

    def test_auth_basic(self):
        url = "http://demo.sjoerdlangkemper.nl/auth/basic.php"
        self.assertIn("Authorization header missing", requests.get(url).text)
        self.assertIn("Authorization header received", requests.get(url, auth=requests.auth.HTTPBasicAuth("a", "a")).text)

if __name__ == "__main__":
    unittest.main()
