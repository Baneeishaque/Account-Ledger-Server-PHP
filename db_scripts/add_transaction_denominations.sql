-- Add cash-denomination count columns to transactionsv2
-- Old rows keep NULL (unaffected); old clients that omit the params
-- store NULL on insert / keep existing values on update.

ALTER TABLE `transactionsv2`
  ADD COLUMN `notes_500` INT NULL DEFAULT NULL AFTER `to_account_id`,
  ADD COLUMN `notes_200` INT NULL DEFAULT NULL AFTER `notes_500`,
  ADD COLUMN `notes_100` INT NULL DEFAULT NULL AFTER `notes_200`,
  ADD COLUMN `notes_50`  INT NULL DEFAULT NULL AFTER `notes_100`,
  ADD COLUMN `notes_20`  INT NULL DEFAULT NULL AFTER `notes_50`,
  ADD COLUMN `notes_10`  INT NULL DEFAULT NULL AFTER `notes_20`,
  ADD COLUMN `notes_5`   INT NULL DEFAULT NULL AFTER `notes_10`,
  ADD COLUMN `notes_2`   INT NULL DEFAULT NULL AFTER `notes_5`,
  ADD COLUMN `notes_1`   INT NULL DEFAULT NULL AFTER `notes_2`,
  ADD COLUMN `coins_20`  INT NULL DEFAULT NULL AFTER `notes_1`,
  ADD COLUMN `coins_10`  INT NULL DEFAULT NULL AFTER `coins_20`,
  ADD COLUMN `coins_5`   INT NULL DEFAULT NULL AFTER `coins_10`,
  ADD COLUMN `coins_2`   INT NULL DEFAULT NULL AFTER `coins_5`,
  ADD COLUMN `coins_1`   INT NULL DEFAULT NULL AFTER `coins_2`,
  ADD COLUMN `coins_0_5` INT NULL DEFAULT NULL AFTER `coins_1`;
