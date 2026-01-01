# 🕶️ JumpBox – Intentionally Vulnerable Apache Web Application

![JumpBox Control Panel](assets/JumpBox.png)

## Overview

**JumpBox** is an **intentionally vulnerable PHP web application** designed to run on an **Ubuntu-based Apache web server**.  
The application simulates a realistic **initial access jump box**, commonly identified during external penetration tests and security assessments.

The purpose of this project is to demonstrate how **common web application weaknesses** on a publicly reachable Linux host can lead to credential exposure and system-level footholds.

> ⚠️ This application is intentionally insecure and must **only** be used in isolated lab environments.

---

## Lab Description

JumpBox represents a simple, externally accessible web server that exposes multiple insecure endpoints.  
From an attacker’s perspective, it behaves like a low-effort external target discovered during reconnaissance.

Key characteristics:

- Ubuntu Linux (assumed pre-installed and configured)
- Apache with PHP support
- Multiple intentionally insecure application endpoints
- Sensitive backup artifact exposed via application logic

Once compromised, the server represents an attacker-controlled **jump box** within the environment.

---

## Repository Usage

This repository contains **only the web application files**.  
It assumes:

- Ubuntu is already installed
- Apache and PHP are already configured and running
- The user understands basic Linux web server administration

To use the project:

```bash
git clone https://github.com/nguyenbrandonm/Vulnerable-Webserver.git
cd Vulnerable-Webserver
