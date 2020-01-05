import React from "react";
import api from "./api";

export default function DictPage(props) {
  const {
    match: {
      params: { id }
    }
  } = props;

  const [dict, setDict] = React.useState(null);
  React.useEffect(() => {
    api.dict(id).then(setDict);
  }, [id]);

  const [saving, setSaving] = React.useState(false);

  if (!dict) {
    return "...";
  }

  return (
    <form
      onSubmit={async e => {
        e.preventDefault();
        const name = e.target.querySelector('[name="name"]');
        const lookupURLTemplate = e.target.querySelector(
          '[name="lookupURLTemplate"]'
        );
        setSaving(true);
        await api.updateDict(dict.id, {
          name: name.value,
          lookupURLTemplate: lookupURLTemplate.value
        });
        setSaving(false);
      }}
    >
      <div>
        <label>Name</label>
        <input name="name" defaultValue={dict.name} />
      </div>
      <div>
        <label>World lookup URL template</label>
        <input name="lookupURLTemplate" defaultValue={dict.lookupURLTemplate} />
        <br />
        <small>
          For example, {"https://de.wiktionary.org/w/index.php?search={{word}}"}
        </small>
      </div>
      <div>
        <button type="submit" disabled={saving}>
          Save
        </button>
      </div>
    </form>
  );
}
