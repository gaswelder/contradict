const backend = "http://localhost:8080";

async function authFetch(url, options = {}) {
  const r = await fetch(backend + url, {
    credentials: "include",
    ...options
  });
  if (r.status == 401) {
    const e = new Error("unauthorized");
    e.unauthorized = true;
    throw e;
  }
  return r;
}

async function getJSON(url) {
  const r = await authFetch(url);
  return r.json();
}

function postData(val) {
  if (Array.isArray(val)) {
    return val.map(([k, v]) => `${k}=${encodeURIComponent(v)}`).join("&");
  }
  if (typeof val == "object") {
    return postData(Object.entries(val));
  }
  throw new Error("unknown POST payload type: " + typeof val);
}

async function post(url, data) {
  const r = await authFetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: postData(data)
  });
  return r;
}

export default {
  async login(password) {
    const r = await post("/login", { password }).then(r => r.text());
    if (r != "ok") {
      throw new Error("auth failed");
    }
  },

  logout() {
    return post("/logout", {}).then(r => r.text());
  },

  dicts() {
    return getJSON("/");
  },

  test(dictID) {
    return getJSON(`/${dictID}/test`);
  },

  submitAnswers(dictID, entries) {
    // console.log(entries);
    // [
    //   ["q[]", "69"],
    //   ["dir[]", "0"],
    //   ["a[]", "hand"],
    //   ["q[]", "496"],
    //   ["dir[]", "0"],
    //   ["a[]", ""]
    // ];
    return post(`/${dictID}/test`, entries).then(r => r.json());
  },

  entry(id) {
    return getJSON(`/entries/${id}`);
  },

  updateEntry(id, entry) {
    return post(`/entries/${id}`, { q: entry.q, a: entry.a });
  },

  addEntries(dictID, string) {
    return post(`/${dictID}/add`, { words: string });
  }
};
