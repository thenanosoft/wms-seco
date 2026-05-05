# GitHub se code lene ke baad (pehle se data / DB maujood ho)

Agar aapke system par program pehle se chal raha hai aur database mein data hai, to **seed mat chalao**, **migrate:fresh mat chalao**. Sirf nayi migrations chalao.

---

## Option A: Git se clone (agar git installed hai)

```bash
# Jahan project rakhna hai wahan jaayein
cd /path/to/your/projects

# Repo clone karein (apna repo URL use karein)
git clone https://github.com/YOUR_USERNAME/wms.git
cd wms
```

---

## Option B: ZIP download karke extract

1. GitHub par repo kholen → **Code** → **Download ZIP**
2. ZIP extract karein (jaise `wms` folder mein)
3. Terminal open karke us folder mein jaayein:
   ```bash
   cd /path/to/extracted/wms
   ```

---

## Dono options ke baad ye steps same hain

### 1. Dependencies install karein

```bash
composer install
```

(NPM/Node agar frontend build ke liye use ho to: `npm install` / `npm run build`)

### 2. Cache clear (ZIP extract ke baad aksar zaroori)

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

Agar phir bhi config/error aaye to ek baar ye bhi chala lo:
```bash
php artisan config:cache
```

**Windows:** project folder mein `clear_cache.bat` double-click se bhi yehi commands chal jati hain.

### 3. Environment file

- Agar pehle se `.env` hai to **same folder** mein rakhen (extract/clone wale folder ke andar).
- Naya setup ho to:
  ```bash
  cp .env.example .env
  php artisan key:generate
  ```
  Phir `.env` mein database name, user, password apne hisaab se set karein.

### 4. Sirf nayi migrations chalao (data safe rahega)

```bash
php artisan migrate
```

Ye **sirf abhi tak chali hui migrations ke baad wali nayi migrations** chalayega. Purana data delete nahi hoga.

### 5. Ye mat chalao

- `php artisan db:seed` — seed **mat** chalao (agar existing users/data hai)
- `php artisan migrate:fresh` — **mat** chalao (poora DB drop ho jata hai)
- `php artisan migrate:fresh --seed` — **mat** chalao

---

## Short summary

| Kaam              | Command / action        |
|-------------------|-------------------------|
| Code lena         | Git clone **ya** ZIP download + extract |
| Cache clear       | `php artisan cache:clear` + `config:clear` + `view:clear` + `route:clear` (ya `clear_cache.bat`) |
| Dependencies      | `composer install`      |
| Nayi migrations   | `php artisan migrate`   |
| Seed / DB wipe    | **Mat chalao**          |

Is tarah GitHub se update lene par sirf naya code aur nayi migrations lagengi, database ka purana data same rahega.
