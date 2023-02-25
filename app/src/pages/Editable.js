import React, { useState } from "react";
import { LinkButton } from "../components/LinkButton";

export const Editable = ({ content, onChange }) => {
  const [editing, setEditing] = useState(false);
  const [val, setVal] = useState("");
  return (
    <div>
      {editing ? (
        <>
          <textarea
            value={val}
            onChange={(e) => {
              setVal(e.target.value);
            }}
          ></textarea>
          <LinkButton
            onClick={() => {
              onChange(val);
              setEditing(false);
            }}
          >
            Save
          </LinkButton>
        </>
      ) : (
        <>
          {content}
          <LinkButton
            onClick={() => {
              setEditing(true);
              setVal(content);
            }}
          >
            Edit
          </LinkButton>
        </>
      )}
    </div>
  );
};
