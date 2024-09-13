const backend = process.env.BACKEND_URL || "http://localhost:8080/api";
export const ROOT_PATH = process.env.ROOT_PATH || "/";

async function authFetch(url, options = {}) {
  const r = await fetch(backend + url, {
    credentials: "include",
    ...options,
  });
  if (r.status == 401) {
    const e = new Error("unauthorized");
    e.unauthorized = true;
    throw e;
  }
  if (r.status >= 400) {
    throw new Error(`${url}: status ${r.status}: ${await r.text()}`);
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
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: postData(data),
  });
  return r;
}

function postJSON(url, data) {
  return authFetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });
}

export default {
  async login(login, password) {
    const response = await post("/login", { login, password });
    const body = await response.text();
    if (response.status != 201) {
      throw new Error("login failed: " + body);
    }
  },

  logout() {
    return post("/logout", {}).then((r) => r.text());
  },

  dicts() {
    return getJSON("/");
  },
  createDict: (name) => postJSON(`/`, { name }),

  async dict(id) {
    const dicts = await this.dicts();
    return dicts.find((d) => d.id == id);
  },

  updateDict: (id, body) => postJSON(`/${id}`, body),
  sheet: (dictID) => getJSON(`/${dictID}/sheet?size=1000`),
  test: (dictID, size) => getJSON(`/${dictID}/test?size=` + size),
  entry: (dictID, id) => getJSON(`/entries/${dictID}/${id}`),

  updateEntry: (dictID, id, entry) =>
    post(`/entries/${dictID}/${id}`, { q: entry.q, a: entry.a }),
  deleteEntry: (dictID, id) => post(`/delete/${dictID}/${id}`, {}),

  addEntries: (dictID, entries) =>
    postJSON(`/${dictID}/add`, {
      entries: entries.map((e) => [e.q, e.a]),
    }).then((r) => r.json()),

  dump: () => getJSON("/export"),
  load: (data) => postJSON("/export", data),
  touchCard: (dictID, entryID, success) =>
    postJSON(`/touch/${dictID}/${entryID}`, { success }),
};
