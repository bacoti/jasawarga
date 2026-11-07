#!/usr/bin/env python3
"""Unauthenticated localhost HTTP smoke tests without dependencies/credentials.

Usage: python scripts/stage7_http_smoke.py --base-url http://127.0.0.1:8000
  --include-db additionally checks public PostgreSQL catalog / reviews (migrations required).
Never tests admin login credentials. Uses only synthetic form values.
"""
import argparse
import http.cookiejar
import json
import sys
import urllib.error
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--base-url', default='http://127.0.0.1:8000')
    parser.add_argument('--include-db', action='store_true')
    parser.add_argument('--json', action='store_true')
    args = parser.parse_args()
    base = args.base_url.rstrip('/')
    parsed = urllib.parse.urlparse(base)
    if parsed.scheme not in ('http', 'https') or not parsed.netloc:
        parser.error('--base-url harus URL HTTP(S) lengkap')

    class NeverRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, request, fp, code, msg, headers, newurl):
            return None

    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(
        urllib.request.HTTPCookieProcessor(jar), NeverRedirect()
    )

    def request(method, path, data=None):
        body = None if data is None else urllib.parse.urlencode(data).encode('utf-8')
        req = urllib.request.Request(base + path, data=body, method=method,
                                     headers={'Accept': 'text/html', 'User-Agent': 'JasaWarga-Stage7-Smoke/1.0'})
        try:
            with opener.open(req, timeout=12) as response:
                return response.status, response.headers.get('Location', '')
        except urllib.error.HTTPError as err:
            return err.code, err.headers.get('Location', '')

    checks = [
        ('home_public', 'GET', '/', None, [200]),
        ('login_public', 'GET', '/masuk', None, [200]),
        ('admin_guest_redirect', 'GET', '/admin/dashboard', None, [302]),
        ('mitra_guest_redirect', 'GET', '/mitra/layanan-db', None, [302]),
        ('warga_guest_redirect', 'GET', '/akun', None, [302]),
        ('review_write_guest_redirect', 'GET', '/ulasan-db/tulis', None, [302]),
        ('csrf_login_post_without_token', 'POST', '/masuk',
         {'email': 'stage7-test@example.invalid', 'password': 'not-a-real-password', 'role': 'citizen'}, [419]),
    ]
    if args.include_db:
        checks += [
            ('catalog_db_public', 'GET', '/katalog-jasa', None, [200]),
            ('review_db_public', 'GET', '/ulasan-db', None, [200]),
        ]
    results = []
    for name, method, path, data, expected in checks:
        try:
            status, location = request(method, path, data)
            passed = status in expected
            if name.endswith('guest_redirect') and passed:
                target = urllib.parse.urlparse(location).path
                passed = target == '/masuk'
            results.append({'name': name, 'pass': passed,
                            'status': status, 'expected': expected})
        except (TimeoutError, OSError, urllib.error.URLError) as err:
            results.append({'name': name, 'pass': False, 'status': None,
                            'expected': expected, 'error': type(err).__name__})
    if args.json:
        print(json.dumps({'base_url': base, 'checks': results}, indent=2))
    else:
        for item in results:
            print(f"[{'PASS' if item['pass'] else 'FAIL'}] {item['name']}: HTTP {item['status']}, expected {item['expected']}")
        total = sum(r['pass'] for r in results)
        print(f"Hasil smoke: {total}/{len(results)} PASS. Hanya localhost/sesi guest; belum uji E2E akun.")
    return 0 if all(row['pass'] for row in results) else 1


if __name__ == '__main__':
    sys.exit(main())
