-- Warehouse WMS Sample Data (MySQL)
-- Import AFTER running migrations.
-- Password for users: password

SET FOREIGN_KEY_CHECKS=0;

TRUNCATE TABLE stock_ledger;
TRUNCATE TABLE issue_return_lines;
TRUNCATE TABLE issue_returns;
TRUNCATE TABLE return_lines;
TRUNCATE TABLE return_transactions;
TRUNCATE TABLE issue_lines;
TRUNCATE TABLE issues;
TRUNCATE TABLE purchase_lines;
TRUNCATE TABLE purchases;
TRUNCATE TABLE items;
TRUNCATE TABLE groups;
TRUNCATE TABLE app_settings;
TRUNCATE TABLE users;

INSERT INTO users (id,name,email,role,password,created_at,updated_at) VALUES
(1,'Admin','admin@local.test','admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW(),NOW()),
(2,'Helper','helper@local.test','store_helper','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',NOW(),NOW());

INSERT INTO app_settings (id,`key`,`value`,created_at,updated_at) VALUES
(1,'default_low_stock_threshold','10',NOW(),NOW());

INSERT INTO groups (id,group_code,group_name,created_at,updated_at) VALUES
(1,'51','Steel Material',NOW(),NOW()),
(2,'52','Electric Items',NOW(),NOW());

INSERT INTO items (id,group_id,item_code,name,default_spec,created_at,updated_at,low_stock_threshold) VALUES
(1,1,'5101','Steel Rod','10mm',NOW(),NOW(),20),
(2,1,'5102','Steel Sheet','1mm',NOW(),NOW(),5),
(3,2,'5201','Copper Wire','1 core',NOW(),NOW(),50);

-- One purchase with 2 lines
INSERT INTO purchases (id,purchase_date,supplier_name,reference_no,created_by,notes,created_at,updated_at) VALUES
(1,CURDATE(),'Local Supplier','PO-1001',1,'Demo purchase',NOW(),NOW());

INSERT INTO purchase_lines (id,purchase_id,item_id,specification,purchase_price,quantity,line_total,created_at,updated_at) VALUES
(1,1,1,'10mm',150.00,100.000,15000.00,NOW(),NOW()),
(2,1,2,'1mm',300.00,10.000,3000.00,NOW(),NOW());

-- Stock ledger for purchase
INSERT INTO stock_ledger (id,txn_type,txn_date,ref_table,ref_id,ref_line_id,item_id,qty_in,qty_out,unit_price,line_total,created_by,created_at,updated_at) VALUES
(1,'PURCHASE',CURDATE(),'purchase_lines',1,1,1,100.000,0.000,150.00,15000.00,1,NOW(),NOW()),
(2,'PURCHASE',CURDATE(),'purchase_lines',1,2,2,10.000,0.000,300.00,3000.00,1,NOW(),NOW());

-- One issue with 1 line
INSERT INTO issues (id,issue_date,issued_to,reference_no,created_by,notes,created_at,updated_at) VALUES
(1,CURDATE(),'Production','IS-2001',2,'Demo issue',NOW(),NOW());

INSERT INTO issue_lines (id,issue_id,item_id,specification,issue_price,quantity,line_total,created_at,updated_at) VALUES
(1,1,1,'10mm',160.00,20.000,3200.00,NOW(),NOW());

INSERT INTO stock_ledger (id,txn_type,txn_date,ref_table,ref_id,ref_line_id,item_id,qty_in,qty_out,unit_price,line_total,created_by,created_at,updated_at) VALUES
(3,'ISSUE',CURDATE(),'issue_lines',1,1,1,0.000,20.000,160.00,3200.00,2,NOW(),NOW());

-- Return against the issue (5 qty)
INSERT INTO issue_returns (id,return_date,issue_id,received_from,reference_no,notes,created_by,created_at,updated_at) VALUES
(1,CURDATE(),1,'Production','IR-3001','Demo issue return',2,NOW(),NOW());

INSERT INTO issue_return_lines (id,issue_return_id,issue_line_id,item_id,specification_snapshot,quantity,unit_price,line_total,created_at,updated_at) VALUES
(1,1,1,1,'10mm',5.000,160.00,800.00,NOW(),NOW());

INSERT INTO stock_ledger (id,txn_type,txn_date,ref_table,ref_id,ref_line_id,item_id,qty_in,qty_out,unit_price,line_total,created_by,created_at,updated_at) VALUES
(4,'ISSUE_RETURN_IN',CURDATE(),'issue_return_lines',1,1,1,5.000,0.000,160.00,800.00,2,NOW(),NOW());

SET FOREIGN_KEY_CHECKS=1;
