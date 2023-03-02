import React from "react";
import Resource from "./Resource";
import withAPI from "./withAPI";

export const DictPage = withAPI(({ busy, api, dictID }) => {
  return (
    <Resource getPromise={() => api.dict(dictID)}>
      {(dict) => {
        if (!dict) {
          return "...";
        }
        return (
          <form
            onSubmit={async (e) => {
              e.preventDefault();
              const name = e.target.querySelector('[name="name"]');
              const lookupURLTemplates = e.target.querySelector(
                '[name="lookupURLTemplates"]'
              );
              await api.updateDict(dict.id, {
                name: name.value,
                lookupURLTemplates: lookupURLTemplates.value
                  .split(/\n/)
                  .map((line) => line.trim())
                  .filter((line) => line != ""),
              });
            }}
          >
            <div>
              <label>Name</label>
              <input name="name" defaultValue={dict.name} />
            </div>
            <div>
              <label>World lookup URL templates</label>
              <textarea
                name="lookupURLTemplates"
                defaultValue={dict.lookupURLTemplates.join("\n")}
              />
              <br />
              <small>
                For example,{" "}
                {"https://de.wiktionary.org/w/index.php?search={{word}}"}
              </small>
            </div>
            <div>
              <button type="submit" disabled={busy}>
                Save
              </button>
            </div>
          </form>
        );
      }}
    </Resource>
  );
});
