#!/bin/bash

php --version

# ================= BACKEND REGISTRATION ================= #
if pgrep -f "registerBackend.php" > /dev/null;
then
	echo "BACKEND IS RUNNING { registerBackend.php }"
else
	echo "[registerBackend.php] IS NOT RUNNING\n"
	echo "FILE HAS BEEN ACTIVATED"
	xterm -e "php registerBackend.php"
fi


# ================= BACKEND LOGIN ================= #
if pgrep -f "loginBackend.php" > /dev/null;
then
        echo "BACKEND IS RUNNING { loginBackend.php }"
else
        echo "[loginBackend.php] IS NOT RUNNING\n"
        echo "FILE HAS BEEN ACTIVATED"
        gnome-terminal -- php loginBackend.php
fi


# ================= BACKEND FEEDBACK ================= #
if pgrep -f "feedbackBackend.php" > /dev/null;
then
        echo "BACKEND IS RUNNING { feedbackBackend.php }"
else
        echo "[feedbackBackend.php] IS NOT RUNNING\n"
        echo "FILE HAS BEEN ACTIVATED"
        gnome-terminal -- php feedbackBackend.php
fi




