# 🚨 CRITICAL DATABASE SAFETY RULES

## ⚠️ **NEVER RUN RefreshDatabase TESTS ON PRODUCTION DATA**

### What Happened
- Tests with `RefreshDatabase` trait were run on a database containing real data
- This **PERMANENTLY DELETED** all existing data including:
  - 308 imported clients
  - 23 imported lawyers  
  - All user accounts
  - All other production data

### The Rule
**NEVER** use `RefreshDatabase` trait on databases containing real/production data.

### Safe Testing Alternatives
1. **Separate Test Database**: Use `.env.testing` with different database
2. **DatabaseTransactions**: Use `DatabaseTransactions` trait instead
3. **Mocked Operations**: Mock database operations in tests
4. **Ask Permission**: Always ask before running database tests

### Implementation
This rule is now permanently added to:
- `docs/Agent_Rules.md` (High-Priority Prohibitions)
- `docs/Agent_Rules.md` (Testing Requirements)
- This warning file

### Recovery
Data was restored from backup `litigation_db_ver2 (14Oct2025-1PM).sql`:
- ✅ 11 users restored
- ✅ 308 clients restored  
- ✅ 23 lawyers restored

### Prevention
- Always check if database contains real data before running tests
- Use separate test environments
- Ask permission before any database operations
- Never assume it's safe to run tests on production data

---
**This rule is PERMANENT and applies to ALL future agents working on this project.**
