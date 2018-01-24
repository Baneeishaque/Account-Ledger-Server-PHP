SELECT * FROM transactionsv2 WHERE particulars LIKE '%Conversion%';
SELECT * FROM transactionsv2 WHERE particulars LIKE '%Convversion%';
UPDATE transactionsv2 SET particulars = REPLACE(particulars, 'Convversion', 'Conversion') WHERE particulars LIKE '%Convversion%';
SELECT * FROM transactionsv2 WHERE particulars LIKE '%Convversion%';
SELECT * FROM transactionsv2 WHERE particulars LIKE '%Conversion%';

