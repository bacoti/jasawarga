import test from 'node:test';
import assert from 'node:assert/strict';
import {buildAdminReport,validatePeriod,inPeriod,makeReportCsv,safeCsvCell} from '../../resources/js/admin-reports-data.js';
const now = new Date('2026-11-07T12:00:00').getTime();
const at = (days=0) => now-days*86400000;
const orders=[
 {reference:'JW-DEMO-20261107-A1B2C3',providerId:1,provider:'Budi Teknik',service:'Cuci AC',price:75000,status:'pending',createdAt:at(),history:[{status:'pending',at:at()}]},
 {reference:'JW-DEMO-20261106-A1B2C4',providerId:1,provider:'Budi Teknik',service:'Cuci AC',price:95000,status:'completed',createdAt:at(1),history:[{status:'pending',at:at(1)},{status:'completed',at:at()}]},
 {reference:'JW-DEMO-20261015-A1B2C5',providerId:2,provider:'Pak Joko',service:'Saluran',price:60000,status:'completed',createdAt:at(23),history:[{status:'pending',at:at(23)},{status:'completed',at:at(2)}]},
 {reference:'JW-DEMO-20261104-A1B2C6',providerId:2,provider:'Pak Joko',service:'Saluran',price:50000,status:'rejected',createdAt:at(3),history:[{status:'pending',at:at(3)}]},
];
const reviews=[
 {reference:orders[1].reference,rating:5,providerId:1,createdAt:at(1)},
 {reference:orders[2].reference,rating:2,providerId:2,createdAt:at(4)},
];
const source={orders,reviews,accounts:[{createdAt:at(1)},{createdAt:at(80)}],providers:[{id:1,name:'Budi Teknik',category:'ac'},{id:2,name:'Pak Joko',category:'pipa'}],categories:[{slug:'ac',name:'Servis AC'},{slug:'pipa',name:'Air & Pipa'}],serviceCounts:[{activeCount:3},{activeCount:1}],feedback:{reviews:{[orders[2].reference]:{status:'hidden',at:at(1)}},cases:[{id:'JW-ADU-20261107-A1B2C3',subject:'Case',createdAt:at(1),status:'investigating',topic:'service',priority:'high',reference:orders[1].reference}],audit:[{at:at(1),action:'case:created'}]},citizenAudit:{log:[{at:at(3),previous:'active',next:'inactive'}]},partnerAudit:{log:[]}};

test('periode 7/30 hari dan filter tanggal menerima hanya rentang yang sesuai',()=>{
 assert.deepEqual(validatePeriod({period:'7'},now),{period:'7',from:'2026-11-01',to:'2026-11-07'});
 assert.equal(inPeriod(at(3),validatePeriod({period:'7'},now)),true);
 assert.equal(inPeriod(at(23),validatePeriod({period:'7'},now)),false);
 assert.equal(validatePeriod({period:'all'},now).from,null);
});
test('tanggal khusus menolak format salah, terbalik, masa depan, dan >366 hari',()=>{
 assert.throws(()=>validatePeriod({period:'custom',from:'2026-10-10',to:'2026-10-09'},now),/tidak valid/);
 assert.throws(()=>validatePeriod({period:'custom',from:'2026-02-30',to:'2026-11-07'},now),/tidak valid/);
 assert.throws(()=>validatePeriod({period:'custom',from:'2026-10-01',to:'2026-12-01'},now),/hari ini/);
 assert.throws(()=>validatePeriod({period:'custom',from:'2025-01-01',to:'2026-11-07'},now),/maksimal/);
 assert.throws(()=>validatePeriod({period:'custom',from:'2026-10-01',to:'x'},now),/tidak valid/);
});
test('periode 7 hari menjaga denominasi pesanan dibuat dan event selesai terpisah',()=>{
 const report=buildAdminReport(source,{period:'7'},now);
 assert.equal(report.metrics.orders,3);
 assert.equal(report.metrics.completed,1);
 assert.equal(report.metrics.completedEvents,2);
 assert.equal(report.metrics.completionRate,33);
 assert.equal(report.metrics.estimatedCompletedTariff,155000);
 assert.equal(report.metrics.catalogProvidersDemo,2);
 assert.equal(report.metrics.servicesDemo,4);
 assert.equal(report.chart.length,7);
 assert.equal(report.chart.reduce((n,d)=>n+d.completed,0),2);
});
test('rating hanya ulasan terlihat; ulasan asli tidak dihapus dari total',()=>{
 const report=buildAdminReport(source,{period:'7'},now);
 assert.equal(report.metrics.reviews,2);
 assert.equal(report.metrics.visibleReviews,1);
 assert.equal(report.metrics.averageRating,5);
 assert.equal(report.metrics.complaints,1);
 assert.equal(report.metrics.complaintsOpen,1);
 assert.equal(report.metrics.newAccounts,1);
});
test('laporan grup Mitra dan kategori dari ID katalog bukan nama pemesan',()=>{
 const report=buildAdminReport(source,{period:'7'},now);
 assert.equal(report.topProviders[0].label,'Budi Teknik');
 assert.equal(report.topProviders[0].orders,2);
 assert.equal(report.topCategories[0].label,'Servis AC');
 assert.equal(report.topCategories[1].label,'Air & Pipa');
 assert.equal(report.audits.length,2);
});
test('periode semua data menghitung seluruh riwayat namun grafik hanya 30 hari',()=>{
 const report=buildAdminReport(source,{period:'all'},now);
 assert.equal(report.metrics.orders,4);
 assert.equal(report.chart.length,30);
 assert.equal(report.chartBounded,true);
});
test('kosong aman termasuk pembagi nol dan ekspor ringkasan',()=>{
 const report=buildAdminReport({}, {period:'7'},now);
 assert.equal(report.metrics.completionRate,0);
 assert.equal(report.metrics.averageRating,0);
 assert.equal(report.chart.length,7);
 assert.match(makeReportCsv('summary',report),/orders/);
});
test('CSV tidak memuat identitas warga, alamat, isi pengaduan, dan aman dari formula',()=>{
 const poisoned=structuredClone(source);
 poisoned.orders[0].service='=HYPERLINK("bad")';
 poisoned.orders[0].address='Jalan Rahasia';
 poisoned.orders[0].customerPhone='08123456789';
 poisoned.feedback.cases[0].description='Deskripsi sangat rahasia';
 const report=buildAdminReport(poisoned,{period:'30'},now);
 const csv=makeReportCsv('orders',report);
 assert.ok(csv.startsWith('\ufeff'));
 assert.match(csv,/"'=HYPERLINK/);
 assert.equal(csv.includes('Jalan Rahasia'),false);
 assert.equal(csv.includes('08123456789'),false);
 assert.equal(makeReportCsv('complaints',report).includes('Deskripsi sangat rahasia'),false);
 assert.equal(makeReportCsv('orders',report,{includeEstimatedTariff:false}).includes('95000'),false);
 assert.equal(makeReportCsv('summary',report,{includeEstimatedTariff:false}).includes('estimatedCompletedTariff'),false);
 assert.equal(safeCsvCell('@malicious'),'"\'@malicious"');
 assert.throws(()=>makeReportCsv('other',report),/tidak dikenali/);
});
