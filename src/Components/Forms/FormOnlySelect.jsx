import { ErrorBoundary } from "react-error-boundary";
import Form from "react-bootstrap/Form";
import PropTypes from "prop-types";

import { ErrorFallback } from "@/Errors";

const FormOnlySelect = ({ data, state, handleChange }) => {
  return (
    <>
      <ErrorBoundary FallbackComponent={ErrorFallback}>
        <Form>
          <Form.Select aria-label="Choose request type" value={state} onChange={handleChange}>
            {data.map((option) => {
              return (
                <option key={option.value} value={option.value}>
                  {option.text}
                </option>
              );
            })}
          </Form.Select>
        </Form>
      </ErrorBoundary>
    </>
  );
};

FormOnlySelect.propTypes = {
  data: PropTypes.array.isRequired,
  state: PropTypes.string.isRequired,
  handleChange: PropTypes.func.isRequired,
};

export default FormOnlySelect;