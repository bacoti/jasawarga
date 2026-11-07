#!/usr/bin/env python3
"""Preflight structural checks for JasaWarga's merged Stage 1-7 source; no DB needed."""
from pathlib import Path
import re
import sys

root = Path(__file__).resolve().parents[1]
checks = []

def expect(name, cond):
    checks.append((name, bool(cond)))

migrations = sorted((root/'database/migrations').glob('*.php'))
timestamps = [str(x.name)[:17] for x in migrations]
expect('Distinct migration timestamps', len(set(timestamps)) == len(timestamps))
expect('One users CREATE migration', sum("Schema::create('users'" in x.read_text() for x in migrations) == 1)
expect('One partner_profiles CREATE migration', sum("Schema::create('partner_profiles'" in x.read_text() for x in migrations) == 1)
for name in ['user_profiles', 'user_addresses', 'partner_portfolios', 'user_preferences',
             'service_categories', 'partner_services', 'bookings', 'booking_offers',
             'booking_status_histories', 'service_reviews', 'service_complaints', 'jw_platform_notifications']:
    expect(f'Migration for {name}', any(f"Schema::create('{name}'" in x.read_text() for x in migrations))
routefile = (root/'routes/web.php').read_text()
for route in ['profile-stage2.php', 'booking-stage4.php', 'analytics-stage6.php', 'stage3_catalog.php', 'stage5_feedback.php']:
    expect(f'Route mounted: {route}', f"require __DIR__.'/{route}'" in routefile)
expect('Stage4 scheduler mounted', "console-stage4.php" in (root/'routes/console.php').read_text())
expect('Stage6 booking observer mounted', 'JasaWargaStageSixServiceProvider::class' in (root/'bootstrap/providers.php').read_text())
expect('Stage4 ServiceCatalog uses Stage3 model', 'PartnerService::published()' in (root/'app/Services/Booking/ServiceCatalog.php').read_text())
expect('Stage5 verifier checks completed bookings', "->where('status', Booking::COMPLETED)" in (root/'app/Services/Feedback/ReviewBookingVerifier.php').read_text())
expect('Stage6 analytics service tables aligned', "'partner_services'" in (root/'config/jasawarga-analytics.php').read_text())
expect('Stage6 role storage aligned', "'citizen'" in (root/'app/Services/Analytics/AdminAnalyticsService.php').read_text())
expect('Legacy admin report URL separated', "'/laporan-db'" in (root/'routes/analytics-stage6.php').read_text())
expect('Legacy booking name separated', 'bookings.database.create' in (root/'routes/booking-stage4.php').read_text())
expect('Legacy admin order URL separated', "admin/pesanan-db" in (root/'routes/booking-stage4.php').read_text())
failures = [name for name, passed in checks if not passed]
for name, passed in checks:
    print(f"[{'PASS' if passed else 'FAIL'}] {name}")
print(f"Summary: {len(checks) - len(failures)}/{len(checks)} passed")
if failures: sys.exit(1)
