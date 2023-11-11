sudo systemctl stop apache2 
sudo systemctl stop rabbitmq-server 

sudo systemctl start apache2 
sudo systemctl start rabbitmq-server 

sudo systemctl status apache2 
sudo systemctl status rabbitmq-server
