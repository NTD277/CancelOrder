# CancelOrder

- Version Magento 2.4.1
- Cách pull module về project
  + B1: Mở terminal, chạy : cd /var/www/html/(tên project)/app/code
  + B2: git init
  + B3: git pull https://github.com/NTD277/CancelOrder.git
  + B4: bin/magento c:c && bin/magento s:up && bin/magento s:s:d -f && bin/magento s:d:c. Hoàn thành
- Đề bài: Ở màn hình lịch sử order ở FE: 
  - Hiển thị button cancel với những order đang ở trạng thái chưa hoàn thành
  - Click vào nút cancel thì sẽ:
    + Thực hiện 	validate order: order có tồn tại không, order có đúng là của người dùng đó hay không, trạng thái order đã đúng chưa, nếu invalid thì hiển thị thông báo tương ứng
    + Thực hiện Cancel order, hiển thị thông báo để người dùng biết kết quả thực hiện
- Note: order sẽ hiện "Cancel" <==> status order = "Pending".
