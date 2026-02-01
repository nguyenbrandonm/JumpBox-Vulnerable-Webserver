# 🕶️ JumpBox – Intentionally Vulnerable Web Application

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
- Sensitive backup artifact exposed via application logic (You will need to put this file in yourself, depending on your lab setup)

Once compromised, the server represents an attacker-controlled **jump box** within the environment.

---

## Application Components & Screenshots

The following screenshots illustrate each major component of the JumpBox application.

### 🧭 Dashboard – JumpBox Control Panel

The main landing page that simulates an external-facing jump box.  
Includes navigation tabs and a terminal-style interface with limited command handling and easter eggs.

![Dashboard](assets/screenshots/Index.png)

---

### 📤 File Upload Endpoint

An intentionally vulnerable file upload interface that allows **arbitrary file types**, including server-side scripts, to be uploaded without validation.

![Upload](assets/screenshots/Uploads.png)

---

### 📂 File Viewer Endpoint

An insecure file viewing interface that exposes sensitive files and allows **directory traversal** through user-controlled input.

![File Viewer](assets/screenshots/Viewer.png)

---

### 🌐 Network Ping Utility

A network diagnostic endpoint vulnerable to **command injection**, allowing user input to be passed directly to a system command.

![Ping Utility](assets/screenshots/Ping.png)

---

## Repository Usage

This repository contains **only the web application files**.  
It assumes:

- Ubuntu is already installed
- The user understands basic Linux web server administration

To use the project:

```bash

