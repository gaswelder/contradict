import React, { useEffect, useState } from "react";
import styled from "styled-components";
import { Card } from "./Card";
import { useAPI } from "./withAPI";

const ContainerDiv = styled.div`
  text-align: center;
`;

const ClipDiv = styled.div`
  margin-top: 1em;
  display: flex;
  overflow: hidden;
  & span {
    display: inline-block;
    border: thin solid #cce;
    border-radius: 2px;
    padding: 4px;
    font-size: 8pt;
    line-height: 8px;
    margin: 2px;
  }
`;

export const RepetitionsPage = ({ dictID }) => {
  const { api, busy } = useAPI();
  const [cards, setCards] = useState([]);
  const [show, setShow] = useState(false);
  const [yes, setYes] = useState(false);

  const nextBatch = async () => {
    const r = await api.test(dictID, 20);
    setCards(r.tuples1.map((c) => ({ ...c, inverse: Math.random() < 0.2 })));
  };

  const next = async () => {
    setShow(false);
    setYes(false);
    setCards(cards.slice(1));
  };

  useEffect(() => {
    nextBatch();
  }, []);

  const card = cards[0];
  return (
    <ContainerDiv>
      <ClipDiv>
        {cards.map((card) => (
          <span key={card.id}>{card.score}</span>
        ))}
      </ClipDiv>
      {!cards.length && (
        <button disabled={busy} onClick={nextBatch}>
          Load Next Batch
        </button>
      )}
      {card && (
        <>
          <Card
            card={card}
            show={show}
            inverse={card.inverse}
            onShow={() => {
              if (show) {
                return;
              }
              setShow(true);
              api.touchCard(dictID, card.id, false);
            }}
            onChange={(newCard) => {
              api.updateEntry(dictID, card.id, { q: newCard.q, a: newCard.a });
              setCards([newCard, ...cards.slice(1)]);
            }}
            onDelete={() => {
              api.deleteEntry(dictID, card.id);
              setCards(cards.slice(1));
            }}
          />
          {show && (
            <>
              {yes && (
                <>
                  <button
                    onClick={() => {
                      api.touchCard(dictID, card.id, false);
                      api.touchCard(dictID, card.id, false);
                      next();
                    }}
                  >
                    Oops, wrong guess
                  </button>{" "}
                </>
              )}
              <button onClick={next}>Next</button>
            </>
          )}
          {!show && (
            <>
              <button
                onClick={async () => {
                  api.touchCard(dictID, card.id, false);
                  setShow(true);
                }}
              >
                No, forgot it
              </button>{" "}
              <button
                disabled={busy}
                onClick={() => {
                  api.touchCard(dictID, card.id, true);
                  setShow(true);
                  setYes(true);
                }}
              >
                Yes, know it
              </button>
            </>
          )}
        </>
      )}
    </ContainerDiv>
  );
};
