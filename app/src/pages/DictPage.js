import React from "react";
import Resource from "../components/Resource";
import withAPI from "../components/withAPI";

export const DictPage = withAPI(({ match, busy, api }) => {
  return (
    <Resource getPromise={() => api.dict(match.params.id)}>
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
