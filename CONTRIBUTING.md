 Contribution Rules

 1) Branch Strategy
- يممنوع مباشرة على `main`
- الشغل على `dev` أو على فروع `feature/*`
- لكل مهمة فرع ونسميها باسم واضح 
 2) Commit Rules
- الرسائل واضحة ومختصرة
- مثال :
  - add: create user model
  - fix: wrong total price calculation
  - update: change login validation

 3) Pull Requests
- ممنوع الدمج مباشرة إلى `main`
- نعمل  Pull Request إلى `dev` الاول
- يبعدين بنعمل مراجعة/موافقة
قبل البدء بمهمة جديدة، نتأكد من سحب آخر تحديث (git pull) من فرع dev

 5) Environment & Secrets
- لا يتم رفع ملفات:
  - .env
  - credentials
- استخدموا `.env.example` فقط للمشاركة

## 6) Testing Before Push
- نتأكد من الكود قبل الرفع


