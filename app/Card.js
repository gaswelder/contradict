import React, { Fragment, useState } from "react";
import styled from "styled-components";
import { LinkButton } from "./lib/LinkButton";
import { CardSources } from "./CardSources";

const CardDiv = styled.div`
  padding: 20px;
  box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  border-radius: 4px;
  max-width: 20em;
  border: 1px solid #eef;
  margin: 2em auto;
  background-color: ${(props) => (props.inverse ? "#ffeed9" : "white")};
  position: relative;
  min-height: 6em;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  & .corner {
    position: absolute;
    opacity: 0.7;
    right: 10px;
    top: 10px;
    font-size: 90%;
  }
  & .h {
    font-weight: bold;
  }
`;

export const Card = ({ card, show, onShow, onChange, onDelete, inverse }) => {
  const [editing, setEditing] = useState(false);
  const title = <p className="h">{card.q}</p>;
  const description = (
    <>
      {card.a.split("\n").map((line, i) => (
        <Fragment key={i}>
          {line}
          <br />
        </Fragment>
      ))}
    </>
  );
  return (
    <CardDiv inverse={inverse} onClick={onShow}>
      <div className="corner" title="score">
        {card.score}
      </div>
      {!inverse ? (
        <>
          {title}
          {show && description}
        </>
      ) : (
        <>
          {show && title}
          {description}
        </>
      )}

      {show && (
        <>
          <CardSources card={card} />
          {editing ? (
            <Editor
              card={card}
              onChange={(newCard) => {
                onChange(newCard);
                setEditing(false);
              }}
            />
          ) : (
            <EditButtons
              onEdit={() => setEditing(true)}
              onDelete={() => {
                confirm("delete?") && onDelete();
              }}
            />
          )}
        </>
      )}
    </CardDiv>
  );
};

const EditButtons = ({ onEdit, onDelete }) => {
  return (
    <>
      <LinkButton onClick={onEdit}>Edit</LinkButton>
      <LinkButton
        style={{ position: "absolute", bottom: 0, right: 0, fontSize: "12px" }}
        onClick={onDelete}
      >
        Delete
      </LinkButton>
    </>
  );
};

const Editor = ({ card, onChange }) => {
  const [q, setQ] = useState(card.q);
  const [a, setA] = useState(card.a);
  return (
    <>
      <input
        value={q}
        onChange={(e) => {
          setQ(e.target.value);
        }}
      />
      <br />
      <textarea
        value={a}
        onChange={(e) => {
          setA(e.target.value);
        }}
      />
      <LinkButton
        onClick={() => {
          onChange({ ...card, q, a });
        }}
      >
        Save
      </LinkButton>
    </>
  );
};
