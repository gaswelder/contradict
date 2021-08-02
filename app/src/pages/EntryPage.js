import React, { useState } from "react";
import styled from "styled-components";
import Resource from "../components/Resource";
import withAPI from "../components/withAPI";

const Form = styled.form`
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const EntryPage = ({ match, api, history }) => {
  const [saving, setSaving] = useState(false);

  const id = match.params.id;

  const submit = async (e) => {
    e.preventDefault();
    const f = e.target;
    const q = f.querySelector("[name=q]").value;
    const a = f.querySelector("[name=a]").value;
    setSaving(true);
    await api.updateEntry(id, { q, a });
    setSaving(false);
    history.goBack();
  };

  return (
    <Resource getPromise={() => api.entry(id).then((r) => r.entry)}>
      {(entry) => (
        <Form method="post" onSubmit={submit}>
          <div>
            <label>Q</label>
            <input name="q" defaultValue={entry.q} required />
          </div>
          <div>
            <label>A</label>
            <input name="a" defaultValue={entry.a} required />
          </div>
          <button disabled={saving}>Save</button>
        </Form>
      )}
    </Resource>
  );
};

export default withAPI(EntryPage);
