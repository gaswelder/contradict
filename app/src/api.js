const token = "bed04814f428bf40ef0e";

function authFetch(url, options = {}) {
  return fetch(url, { credentials: "include", ...options });
}

async function getJSON(url) {
  const r = await authFetch(url);
  if (r.status == 401) {
    const e = new Error("unauthorized");
    e.unauthorized = true;
    throw e;
  }
  return r.json();
}

export default {
  async login(password) {
    const r = await authFetch(`http://localhost:8080/login`, {
      method: "post",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: "password=" + password
    });
    if (r.status != 200) {
      throw new Error("login failed");
    }
    await r.text();
  },

  logout() {
    return authFetch("http://localhost:8080/logout", {
      method: "post"
    });
  },

  dicts() {
    return getJSON(`http://localhost:8080?token=${token}`);
  },

  test(dictID) {
    return getJSON(`http://localhost:8080/${dictID}/test?token=${token}`);
  },

  submitAnswers(dictID, entries) {
    const data = entries.map(([k, v]) => `${k}=${v}`).join("&");
    return authFetch(`http://localhost:8080/${dictID}/test?token=${token}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: data
    }).then(r => r.json());
  },

  entry(id) {
    return getJSON(`http://localhost:8080/entries/${id}?token=${token}`);
  },

  updateEntry(id, entry) {
    return authFetch(`http://localhost:8080/entries/${id}?token=${token}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `q=${entry.q}&a=${entry.a}`
    });
  },

  addEntries(dictID, string) {
    return authFetch(`http://localhost:8080/${dictID}/add?token=${token}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `words=${encodeURIComponent(string)}`
    });
  }
};
