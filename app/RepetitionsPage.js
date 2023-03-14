import React, { useEffect, useState } from "react";
import styled from "styled-components";
import withAPI from "./withAPI";
import { Card } from "./Card";

const ContainerDiv = styled.div`
  text-align: center;
`;

const ClipDiv = styled.div`
  margin-top: 1em;
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

export const RepetitionsPage = withAPI(({ api, busy, dictID }) => {
  const [cards, setCards] = useState([]);
  const [show, setShow] = useState(false);
  const [yes, setYes] = useState(false);
  const [count, setCount] = useState(0);
  const [loading, setLoading] = useState(false);

  const nextBatch = async () => {
    setLoading(true);
    const r = await api.test(dictID);
    setLoading(false);
    setCards(r.tuples1.map((c) => ({ ...c, inverse: Math.random() < 0.2 })));
  };

  const next = async () => {
    setCount((x) => x + 1);
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
      {count}
      <ClipDiv>
        {cards.map((card) => (
          <span key={card.id}>{card.score}</span>
        ))}
      </ClipDiv>
      {!cards.length && (
        <button disabled={loading} onClick={nextBatch}>
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
              api.touchCard(dictID, card.id, card.reverse, false);
            }}
            onChange={(newCard) => {
              api.updateEntry(dictID, card.id, { q: newCard.q, a: newCard.a });
              setCards([newCard, ...cards.slice(1)]);
            }}
          />
          {show && (
            <>
              {yes && (
                <>
                  <button
                    onClick={() => {
                      api.touchCard(dictID, card.id, card.reverse, false);
                      api.touchCard(dictID, card.id, card.reverse, false);
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
                  api.touchCard(dictID, card.id, card.reverse, false);
                  setShow(true);
                }}
              >
                No, forgot it
              </button>{" "}
              <button
                disabled={busy}
                onClick={() => {
                  api.touchCard(dictID, card.id, card.reverse, true);
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
});
