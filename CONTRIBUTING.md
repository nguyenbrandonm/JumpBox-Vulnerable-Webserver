# Contributing Guidelines

Thanks for your interest in contributing.

## Project Intent
This repository supports an intentionally vulnerable, **authorized training lab** used for application security and Active Directory security learning in a **controlled and isolated environment**. It is designed for education, validation of security concepts, and defensive improvement workflows.

## Scope of Contributions
This is primarily a portfolio and learning project. Contributions are welcome when they improve:
- Documentation clarity (setup, architecture diagrams, usage notes)
- Lab reliability (build scripts, Docker/VM provisioning, configuration hardening)
- Defensive learning value (detection ideas, logging guidance, remediation notes)
- Safe test scenarios that are clearly explained and non-destructive

## Non-Goals (Not Accepted)
To keep the project safe and appropriate, the following are **not** accepted:
- Instructions or tooling intended for unauthorized access to real systems
- Credential harvesting guidance for real targets
- “Operational playbooks” for lateral movement, network-wide password spraying, or similar activity outside an explicitly isolated lab
- Inclusion of secrets, real credentials, tokens, private keys, or sensitive data

## Safety Requirements
If you submit changes that add new scenarios:
- Ensure they can be executed **only** in an isolated lab (host-only or private network)
- Avoid defaults that expose services directly to the public internet
- Provide a clear disclaimer that the scenario is for authorized training only
- Prefer safe placeholders (example users/passwords) and local-only configs

## How to Contribute
1. Fork the repository
2. Create a feature branch
3. Make your changes with clear commit messages
4. Open a pull request describing:
   - What changed
   - Why it improves the lab
   - Any setup or testing steps

## Reporting Issues
If you find a security weakness in the lab infrastructure itself (not the intended vulnerabilities), open an issue with:
- Steps to reproduce (lab-only)
- Expected vs actual behavior
- Suggested mitigation if available

Maintainers may close issues or PRs that conflict with the safety boundaries above.
